<?php

namespace App\Providers;

use App\Services\ApiRateLimiter;
use App\Services\ProductApiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ProductApiClient::class, function ($app) {
            return new ProductApiClient(
                baseUrl: config('services.product_api.base_url'),
                apiKey: config('services.product_api.api_key'),
            );
        });

        $this->app->singleton(ApiRateLimiter::class, function ($app) {
            return new ApiRateLimiter(
                maxRequests: config('services.product_api.rate_limit', 10),
                identifier: 'product_import',
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
