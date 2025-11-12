<?php

namespace App\Console\Commands;

use App\Actions\ImportProductsAction;
use App\Contracts\CheckpointManagerInterface;
use App\Contracts\FormatterServiceInterface;
use App\DataTransferObjects\ImportStatistics;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\InputOption;

final class ImportProducts extends Command
{
    protected $signature = 'products:import';

    public function __construct(
        private readonly ImportProductsAction $importAction,
        private readonly CheckpointManagerInterface $checkpointManager,
        private readonly FormatterServiceInterface $formatter,
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
        $this->info(__('products.command.starting'));

        try {
            $startPage = $this->determineStartPage();
            $dryRun = (bool) $this->option('dry-run');

            $stats = $this->importAction->execute($startPage, $dryRun);

            $this->checkpointManager->clear();
            $this->displaySummary($stats, $dryRun);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->handleCriticalError($e);

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

    private function handleCriticalError(\Throwable $exception): void
    {
        $this->error(__('products.command.critical_error', ['message' => $exception->getMessage()]));

        Log::channel('import_errors')->critical('Import failed with critical error', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $checkpoint = $this->checkpointManager->get();

        if ($checkpoint !== null) {
            $this->warn("\n".__('products.command.resume_command', ['page' => $checkpoint]));
        }
    }

    private function displaySummary(ImportStatistics $stats, bool $dryRun): void
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

        if ($dryRun) {
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
