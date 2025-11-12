<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Import Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration values for the product import process.
    |
    */

    'checkpoint' => [
        'key' => env('IMPORT_CHECKPOINT_KEY', 'product_import_checkpoint'),
        'ttl_hours' => env('IMPORT_CHECKPOINT_TTL', 24),
    ],

    'retry' => [
        'max_attempts' => env('IMPORT_MAX_RETRIES', 3),
        'delay_ms' => env('IMPORT_RETRY_DELAY_MS', 1000),
    ],

    'batch' => [
        'size' => env('IMPORT_BATCH_SIZE', 100),
    ],

    'recoverable_errors' => [
        'timeout',
        'connection',
        'network',
        'temporarily unavailable',
    ],

];
