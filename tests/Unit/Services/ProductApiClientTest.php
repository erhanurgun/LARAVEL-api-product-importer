<?php

use App\Services\ProductApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('fetches products successfully', function () {
    Http::fake([
        '*' => Http::response([
            'status' => ['code' => 200],
            'data' => [
                'pagination' => [
                    'current_page' => 1,
                    'last_page_url' => 'https://dummyjson.com?page=5',
                    'total' => 500,
                ],
                'products' => [
                    ['id' => 'uuid1', 'title' => 'Product 1', 'slug' => 'prod-1'],
                    ['id' => 'uuid2', 'title' => 'Product 2', 'slug' => 'prod-2'],
                ],
            ],
        ], 200),
    ]);

    $client = new ProductApiClient(
        baseUrl: 'https://dummyjson.com',
        apiKey: 'test-key'
    );

    $response = $client->fetchProducts(page: 1);

    expect($response['data'])->toBeArray()
        ->and($response['data'])->toHaveCount(2)
        ->and($response['current_page'])->toBe(1)
        ->and($response['last_page'])->toBe(5);
});

it('includes authorization header when api key is provided', function () {
    Http::fake([
        '*' => Http::response([
            'data' => ['pagination' => ['current_page' => 1], 'products' => []],
        ], 200),
    ]);

    $client = new ProductApiClient(
        baseUrl: 'https://dummyjson.com',
        apiKey: 'test-key'
    );

    $client->fetchProducts();

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer test-key');
    });
});

it('does not include authorization header when api key is null', function () {
    Http::fake([
        '*' => Http::response([
            'data' => ['pagination' => ['current_page' => 1], 'products' => []],
        ], 200),
    ]);

    $client = new ProductApiClient(
        baseUrl: 'https://dummyjson.com',
        apiKey: null
    );

    $client->fetchProducts();

    Http::assertSent(function ($request) {
        return ! $request->hasHeader('Authorization');
    });
});

it('retries on 429 status code', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push(['error' => 'Rate limit exceeded'], 429)
            ->push(['error' => 'Rate limit exceeded'], 429)
            ->push(['data' => ['pagination' => ['current_page' => 1], 'products' => []]], 200),
    ]);

    $client = new ProductApiClient(
        baseUrl: 'https://dummyjson.com',
        apiKey: 'test-key'
    );

    $response = $client->fetchProducts();

    expect($response['data'])->toBeArray();
    Http::assertSentCount(3);
});

it('retries on 500 server error', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push(['error' => 'Server error'], 500)
            ->push(['error' => 'Server error'], 500)
            ->push(['data' => ['pagination' => ['current_page' => 1], 'products' => []]], 200),
    ]);

    $client = new ProductApiClient(
        baseUrl: 'https://dummyjson.com',
        apiKey: 'test-key'
    );

    $response = $client->fetchProducts();

    expect($response['data'])->toBeArray();
    Http::assertSentCount(3);
});

it('throws exception after max retries', function () {
    Http::fake([
        '*' => Http::response(['error' => 'Server error'], 500),
    ]);

    $client = new ProductApiClient(
        baseUrl: 'https://dummyjson.com',
        apiKey: 'test-key'
    );

    $this->expectException(\Throwable::class);

    $client->fetchProducts();
});
