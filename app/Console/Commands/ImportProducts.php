<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ApiRateLimiter;
use App\Services\ProductApiClient;
use App\Services\ProductDataMapper;
use App\Services\ProductValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\InputOption;

class ImportProducts extends Command
{
    protected $signature = 'products:import';

    private const CHECKPOINT_KEY = 'product_import_checkpoint';

    private int $totalProcessed = 0;

    private int $successfulImports = 0;

    private int $failedValidations = 0;

    private float $startTime = 0;

    private int $startMemory = 0;

    public function __construct(
        private readonly ProductApiClient $apiClient,
        private readonly ProductValidator $validator,
        private readonly ApiRateLimiter $rateLimiter,
        private readonly ProductDataMapper $dataMapper,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(__('products.command.description'))
            ->addOption('resume', null, InputOption::VALUE_NONE, __('products.command.option_resume'))
            ->addOption('dry-run', null, InputOption::VALUE_NONE, __('products.command.option_dry_run'));
    }

    public function handle(): int
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);

        $this->info(__('products.command.starting'));

        try {
            $startPage = $this->option('resume')
                ? Cache::get(self::CHECKPOINT_KEY, 1)
                : 1;

            if (! $this->option('resume')) {
                Cache::forget(self::CHECKPOINT_KEY);
            }

            if ($this->option('resume') && $startPage > 1) {
                $this->info(__('products.command.resuming_from', ['page' => $startPage]));
            }

            $this->importProducts($startPage);

            Cache::forget(self::CHECKPOINT_KEY);

            $this->displaySummary();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->handleCriticalError($e);

            return self::FAILURE;
        }
    }

    private function importProducts(int $startPage): void
    {
        $currentPage = $startPage;
        $lastPage = null;

        do {
            $this->rateLimiter->throttle();

            try {
                $response = $this->apiClient->fetchProducts($currentPage);

                $this->rateLimiter->hit();

                $lastPage = $response['last_page'] ?? 1;
                $products = $response['data'] ?? [];

                if ($currentPage === $startPage) {
                    $totalItems = $response['total'] ?? count($products);
                    $this->line(__('products.command.found_products', ['total' => $totalItems, 'pages' => $lastPage]));
                }

                $this->processPage($products, $currentPage, $lastPage);

                Cache::put(self::CHECKPOINT_KEY, $currentPage + 1, now()->addDay());

                $currentPage++;
            } catch (\Throwable $e) {
                if ($this->isRecoverableError($e)) {
                    $this->warn(__('products.errors.recoverable', ['page' => $currentPage, 'message' => $e->getMessage()]));
                    $this->warn(__('products.errors.waiting_retry'));
                    sleep(5);

                    continue;
                }

                throw $e;
            }
        } while ($currentPage <= $lastPage);
    }

    /**
     * @param  array<array<string, mixed>>  $products
     */
    private function processPage(array $products, int $currentPage, int $lastPage): void
    {
        $this->info("\n".__('products.command.processing_page', ['current' => $currentPage, 'total' => $lastPage]));

        $progressBar = $this->output->createProgressBar(count($products));
        $progressBar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% | Products: %message%'
        );
        $progressBar->setMessage(__('products.command.progress_message', ['successful' => 0, 'failed' => 0]));
        $progressBar->start();

        $validProducts = [];

        foreach ($products as $apiProduct) {
            $this->totalProcessed++;

            $mappedData = $this->dataMapper->mapToDatabaseFormat($apiProduct);

            $result = $this->validator->validateSafe($mappedData, checkUnique: false);

            if ($result['valid']) {
                $validProducts[] = $result['data'];
                $this->successfulImports++;
            } else {
                $this->failedValidations++;
            }

            $progressBar->setMessage(
                __('products.command.progress_message', ['successful' => $this->successfulImports, 'failed' => $this->failedValidations])
            );
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        if (! $this->option('dry-run') && count($validProducts) > 0) {
            $this->saveProducts($validProducts);
        } elseif ($this->option('dry-run')) {
            $this->info(__('products.command.dry_run_message', ['count' => count($validProducts)]));
        }
    }

    /**
     * @param  array<array<string, mixed>>  $products
     */
    private function saveProducts(array $products): void
    {
        try {
            DB::transaction(function () use ($products) {
                $serializedProducts = array_map(function ($product) {
                    if (isset($product['colors']) && is_array($product['colors'])) {
                        $product['colors'] = json_encode($product['colors']);
                    }

                    if (isset($product['all_prices']) && is_array($product['all_prices'])) {
                        $product['all_prices'] = json_encode($product['all_prices']);
                    }

                    if (isset($product['technical_specs']) && is_array($product['technical_specs'])) {
                        $product['technical_specs'] = json_encode($product['technical_specs']);
                    }

                    if (isset($product['user_info']) && is_array($product['user_info'])) {
                        $product['user_info'] = json_encode($product['user_info']);
                    }

                    return $product;
                }, $products);

                Product::upsert(
                    $serializedProducts,
                    uniqueBy: ['slug'],
                    update: [
                        'title', 'content', 'price', 'old_price', 'discount_percentage',
                        'quantity', 'in_stock', 'image_cover', 'image_thumbnail',
                        'container_type', 'container_size', 'production_year', 'condition',
                        'location_city', 'location_district', 'location_country',
                        'type', 'is_new', 'is_hot_sale', 'is_featured', 'is_bulk_sale',
                        'accept_offers', 'status', 'colors', 'all_prices',
                        'technical_specs', 'user_info',
                    ]
                );
            });

            Log::info('Products saved successfully', [
                'count' => count($products),
            ]);
        } catch (\Throwable $e) {
            Log::channel('import_errors')->error('Failed to save products', [
                'error' => $e->getMessage(),
                'count' => count($products),
            ]);

            throw $e;
        }
    }

    private function isRecoverableError(\Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());

        $recoverablePatterns = [
            'timeout',
            'connection',
            'network',
            'temporarily unavailable',
        ];

        foreach ($recoverablePatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function handleCriticalError(\Throwable $exception): void
    {
        $this->error(__('products.command.critical_error', ['message' => $exception->getMessage()]));

        Log::channel('import_errors')->critical('Import failed with critical error', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'total_processed' => $this->totalProcessed,
            'successful_imports' => $this->successfulImports,
            'failed_validations' => $this->failedValidations,
        ]);

        $checkpoint = Cache::get(self::CHECKPOINT_KEY);

        if ($checkpoint !== null) {
            $this->warn("\n".__('products.command.resume_command', ['page' => $checkpoint]));
        }
    }

    private function displaySummary(): void
    {
        $duration = microtime(true) - $this->startTime;
        $memoryUsed = memory_get_usage(true) - $this->startMemory;

        $this->newLine(2);
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('                    '.__('products.summary.title').'                          ');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $this->table(
            [__('products.summary.metric'), __('products.summary.value')],
            [
                [__('products.summary.total_processed'), number_format($this->totalProcessed)],
                [__('products.summary.successful_imports'), number_format($this->successfulImports)],
                [__('products.summary.failed_validations'), number_format($this->failedValidations)],
                [__('products.summary.success_rate'), $this->calculateSuccessRate().'%'],
                [__('products.summary.total_duration'), $this->formatDuration($duration)],
                [__('products.summary.memory_used'), $this->formatBytes($memoryUsed)],
                [__('products.summary.average_time'), $this->calculateAverageTime($duration).'ms'],
            ]
        );

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->warn(__('products.command.dry_run_warning'));
        }

        if ($this->failedValidations > 0) {
            $this->newLine();
            $this->warn(__('products.command.failed_validations_warning', ['count' => $this->failedValidations]));
            $this->warn(__('products.command.check_logs'));
        }

        if ($this->successfulImports > 0) {
            $this->newLine();
            $this->info('✓ '.__('products.command.completed_successfully'));
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
    }

    private function calculateSuccessRate(): string
    {
        if ($this->totalProcessed === 0) {
            return '0.00';
        }

        $rate = ($this->successfulImports / $this->totalProcessed) * 100;

        return number_format($rate, 2);
    }

    private function formatDuration(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm %.2fs', $hours, $minutes, $secs);
        }

        if ($minutes > 0) {
            return sprintf('%dm %.2fs', $minutes, $secs);
        }

        return sprintf('%.2fs', $secs);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes !== 0 ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }

    private function calculateAverageTime(float $totalSeconds): string
    {
        if ($this->totalProcessed === 0) {
            return '0';
        }

        $avgMs = ($totalSeconds / $this->totalProcessed) * 1000;

        return number_format($avgMs, 2);
    }
}
