<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Import Language Lines (English)
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during product import operations.
    |
    */

    'command' => [
        'description' => 'Import products from third-party API with rate limiting and validation',
        'option_resume' => 'Resume from the last checkpoint',
        'option_dry_run' => 'Validate products without saving to database',
        'starting' => 'Starting product import...',
        'resuming_from' => 'Resuming from page :page',
        'found_products' => 'Found :total products across :pages pages',
        'processing_page' => 'Processing page :current of :total',
        'progress_message' => ':successful successful, :failed failed',
        'dry_run_message' => 'Dry run: Would import :count valid products',
        'dry_run_warning' => 'DRY RUN MODE: No data was saved to the database',
        'failed_validations_warning' => ':count products failed validation',
        'check_logs' => 'Check storage/logs/import_errors.log for details',
        'completed_successfully' => 'Import completed successfully',
        'resume_command' => 'You can resume from page :page using: php artisan products:import --resume',
        'critical_error' => 'Critical error during import: :message',
    ],

    'errors' => [
        'recoverable' => 'Recoverable error on page :page: :message',
        'waiting_retry' => 'Waiting 5 seconds before retry...',
    ],

    'summary' => [
        'title' => 'IMPORT SUMMARY',
        'total_processed' => 'Total Products Processed',
        'successful_imports' => 'Successful Imports',
        'failed_validations' => 'Failed Validations',
        'success_rate' => 'Success Rate',
        'total_duration' => 'Total Duration',
        'memory_used' => 'Memory Used',
        'average_time' => 'Average Time per Product',
        'metric' => 'Metric',
        'value' => 'Value',
    ],

    'validation' => [
        'failed' => 'Product validation failed',
    ],

    'status' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],

    'type' => [
        'sale' => 'For Sale',
        'rent' => 'For Rent',
    ],

    'condition' => [
        'new' => 'New',
        'used' => 'Used',
        'refurbished' => 'Refurbished',
    ],

];
