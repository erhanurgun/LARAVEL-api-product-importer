<?php

namespace App\Console\Commands;

use App\Contracts\ApiClientInterface;
use App\Contracts\CheckpointManagerInterface;
use App\Contracts\DataMapperInterface;
use App\Contracts\FormatterServiceInterface;
use App\Contracts\RateLimiterInterface;
use App\Contracts\SerializerInterface;
use App\Contracts\ValidatorInterface;
use App\DataTransferObjects\ImportStatistics;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\InputOption;

final class ImportProducts extends Command
{
    protected $signature = 'products:import';

    public function __construct(
        private readonly ApiClientInterface $apiClient,
        private readonly ValidatorInterface $validator,
        private readonly RateLimiterInterface $rateLimiter,
        private readonly DataMapperInterface $dataMapper,
        private readonly CheckpointManagerInterface $checkpointManager,
        private readonly FormatterServiceInterface $formatter,
        private readonly SerializerInterface $serializer,
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
        $stats = new ImportStatistics(
            startTime: microtime(true),
            startMemory: memory_get_usage(true),
        );

        $this->info(__('products.command.starting'));

        try {
            $startPage = $this->determineStartPage();
            $this->importProducts($startPage, $stats);
            $this->checkpointManager->clear();
            $this->displaySummary($stats);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->handleCriticalError($e, $stats);

            return self::FAILURE;
        }
    }

    private function determineStartPage(): int
    {
        if (! $this->option('resume')) {
            $this->checkpointManager->clear();

            return 1;
        }

        $checkpoint = $this->checkpointManager->get();

        if ($checkpoint !== null && $checkpoint > 1) {
            $this->info(__('products.command.resuming_from', ['page' => $checkpoint]));

            return $checkpoint;
        }

        return 1;
    }

    private function importProducts(int $startPage, ImportStatistics $stats): void
    {
        $currentPage = $startPage;
        $lastPage = null;

        do {
            $this->rateLimiter->throttle();

            try {
                $response = $this->apiClient->fetchProducts($currentPage);
                $this->rateLimiter->hit();

                $lastPage = $response->lastPage;

                if ($currentPage === $startPage) {
                    $this->line(__('products.command.found_products', [
                        'total' => $response->total,
                        'pages' => $lastPage,
                    ]));
                }

                $this->processPage($response->data, $currentPage, $lastPage, $stats);
                $this->checkpointManager->save($currentPage + 1);

                $currentPage++;
            } catch (\Throwable $e) {
                if ($this->isRecoverableError($e)) {
                    $this->warn(__('products.errors.recoverable', [
                        'page' => $currentPage,
                        'message' => $e->getMessage(),
                    ]));
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
    private function processPage(
        array $products,
        int $currentPage,
        int $lastPage,
        ImportStatistics $stats
    ): void {
        $this->info("\n".__('products.command.processing_page', [
            'current' => $currentPage,
            'total' => $lastPage,
        ]));

        $progressBar = $this->output->createProgressBar(count($products));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | Products: %message%');
        $progressBar->setMessage(__('products.command.progress_message', [
            'successful' => 0,
            'failed' => 0,
        ]));
        $progressBar->start();

        $validProducts = [];

        foreach ($products as $apiProduct) {
            $mappedData = $this->dataMapper->mapToDatabaseFormat($apiProduct);
            $result = $this->validator->validateSafe($mappedData, checkUnique: false);

            if ($result['valid']) {
                $validProducts[] = $result['data'];
                $stats->incrementSuccess();
            } else {
                $stats->incrementFailed();
            }

            $progressBar->setMessage(__('products.command.progress_message', [
                'successful' => $stats->successfulImports,
                'failed' => $stats->failedValidations,
            ]));
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
                $serializedProducts = $this->serializer->serializeBatch($products);

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

            Log::info('Products saved successfully', ['count' => count($products)]);
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
        $recoverablePatterns = config('import.recoverable_errors', []);

        foreach ($recoverablePatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function handleCriticalError(\Throwable $exception, ImportStatistics $stats): void
    {
        $this->error(__('products.command.critical_error', ['message' => $exception->getMessage()]));

        Log::channel('import_errors')->critical('Import failed with critical error', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'stats' => $stats,
        ]);

        $checkpoint = $this->checkpointManager->get();

        if ($checkpoint !== null) {
            $this->warn("\n".__('products.command.resume_command', ['page' => $checkpoint]));
        }
    }

    private function displaySummary(ImportStatistics $stats): void
    {
        $this->newLine(2);
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('                    '.__('products.summary.title').'                          ');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $this->table(
            [__('products.summary.metric'), __('products.summary.value')],
            [
                [
                    __('products.summary.total_processed'),
                    $this->formatter->formatNumber($stats->totalProcessed, 0),
                ],
                [
                    __('products.summary.successful_imports'),
                    $this->formatter->formatNumber($stats->successfulImports, 0),
                ],
                [
                    __('products.summary.failed_validations'),
                    $this->formatter->formatNumber($stats->failedValidations, 0),
                ],
                [
                    __('products.summary.success_rate'),
                    $this->formatter->formatNumber($stats->successRate()).'%',
                ],
                [
                    __('products.summary.total_duration'),
                    $this->formatter->formatDuration($stats->duration()),
                ],
                [
                    __('products.summary.memory_used'),
                    $this->formatter->formatBytes($stats->memoryUsed()),
                ],
                [
                    __('products.summary.average_time'),
                    $this->formatter->formatNumber($stats->averageTimePerItem()).'ms',
                ],
            ]
        );

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->warn(__('products.command.dry_run_warning'));
        }

        if ($stats->failedValidations > 0) {
            $this->newLine();
            $this->warn(__('products.command.failed_validations_warning', [
                'count' => $stats->failedValidations,
            ]));
            $this->warn(__('products.command.check_logs'));
        }

        if ($stats->successfulImports > 0) {
            $this->newLine();
            $this->info('✓ '.__('products.command.completed_successfully'));
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
    }
}
