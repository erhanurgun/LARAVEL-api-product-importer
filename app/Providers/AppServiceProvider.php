<?php

namespace App\Providers;

use App\Contracts\ApiClientInterface;
use App\Contracts\CheckpointManagerInterface;
use App\Contracts\DataMapperInterface;
use App\Contracts\FormatterServiceInterface;
use App\Contracts\RateLimiterInterface;
use App\Contracts\SerializerInterface;
use App\Contracts\ValidatorInterface;
use App\Services\ApiRateLimiter;
use App\Services\CheckpointManager;
use App\Services\FormatterService;
use App\Services\ProductApiClient;
use App\Services\ProductDataMapper;
use App\Services\ProductSerializer;
use App\Services\ProductValidator;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerApiServices();
        $this->registerDataServices();
        $this->registerUtilityServices();
    }

    private function registerApiServices(): void
    {
        $this->app->singleton(ApiClientInterface::class, function ($app) {
            return new ProductApiClient(
                baseUrl: config('services.product_api.base_url'),
                apiKey: config('services.product_api.api_key'),
                maxRetries: config('import.retry.max_attempts', 3),
                retryDelayMs: config('import.retry.delay_ms', 1000),
            );
        });

        $this->app->singleton(RateLimiterInterface::class, function ($app) {
            return new ApiRateLimiter(
                maxRequests: config('services.product_api.rate_limit', 10),
                identifier: 'product_import',
            );
        });
    }

    private function registerDataServices(): void
    {
        $this->app->singleton(DataMapperInterface::class, ProductDataMapper::class);
        $this->app->singleton(ValidatorInterface::class, ProductValidator::class);
        $this->app->singleton(SerializerInterface::class, ProductSerializer::class);
    }

    private function registerUtilityServices(): void
    {
        $this->app->singleton(CheckpointManagerInterface::class, function ($app) {
            return new CheckpointManager(
                cacheKey: config('import.checkpoint.key', 'product_import_checkpoint'),
                ttlHours: config('import.checkpoint.ttl_hours', 24),
            );
        });

        $this->app->singleton(FormatterServiceInterface::class, FormatterService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
