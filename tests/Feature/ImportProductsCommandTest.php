<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

it('imports products successfully', function () {
    Http::fake([
        '*' => Http::response([
            'status' => ['code' => 200],
            'data' => [
                'pagination' => [
                    'current_page' => 1,
                    'last_page_url' => 'https://dummyjson.com?page=1',
                    'total' => 2,
                ],
                'products' => [
                    [
                        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
                        'title' => 'Test Product 1',
                        'slug' => 'test-product-1',
                        'content' => 'Test content',
                        'price' => ['current' => 100, 'old' => 120, 'discount' => 17],
                        'stock' => ['quantity' => 10, 'in_stock' => true],
                        'image' => ['cover' => '/cover.jpg', 'thumbnail' => '/thumb.jpg'],
                        'container' => ['types' => ['high_cube'], 'size' => '40HC'],
                        'location' => ['city' => 'Istanbul', 'district' => 'Tuzla', 'country' => 'Turkey'],
                        'type' => 'sale',
                        'status' => 'published',
                        'production_year' => 2024,
                        'condition' => 'new',
                    ],
                    [
                        'id' => 'e020b4af-aff6-427f-8b5d-ca41df9f57a5',
                        'title' => 'Test Product 2',
                        'slug' => 'test-product-2',
                        'content' => 'Test content 2',
                        'price' => ['current' => 200, 'old' => null, 'discount' => null],
                        'stock' => ['quantity' => 20, 'in_stock' => true],
                        'image' => ['cover' => '/cover2.jpg', 'thumbnail' => '/thumb2.jpg'],
                        'container' => ['types' => ['standard'], 'size' => '20ST'],
                        'location' => ['city' => 'Ankara', 'district' => 'Kecioren', 'country' => 'Turkey'],
                        'type' => 'rent',
                        'status' => 'published',
                        'production_year' => 2023,
                        'condition' => 'used',
                    ],
                ],
            ],
        ], 200),
    ]);

    artisan('products:import')
        ->assertSuccessful()
        ->expectsOutput(__('products.command.starting'));

    expect(Product::count())->toBe(2)
        ->and(Product::where('slug', 'test-product-1')->first()->title)->toBe('Test Product 1')
        ->and(Product::where('slug', 'test-product-2')->first()->price)->toBe('200.00');
});

it('handles dry run mode without saving data', function () {
    Http::fake([
        '*' => Http::response([
            'status' => ['code' => 200],
            'data' => [
                'pagination' => [
                    'current_page' => 1,
                    'last_page_url' => 'https://dummyjson.com?page=1',
                    'total' => 1,
                ],
                'products' => [
                    [
                        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
                        'title' => 'Test Product',
                        'slug' => 'test-product',
                        'price' => ['current' => 100],
                        'stock' => ['quantity' => 10, 'in_stock' => true],
                        'status' => 'published',
                    ],
                ],
            ],
        ], 200),
    ]);

    artisan('products:import --dry-run')
        ->assertSuccessful();

    expect(Product::count())->toBe(0);
});

it('resumes from checkpoint', function () {
    Cache::put('product_import_checkpoint', 2, now()->addDay());

    Http::fake([
        '*' => Http::sequence()
            ->push([
                'status' => ['code' => 200],
                'data' => [
                    'pagination' => [
                        'current_page' => 2,
                        'last_page_url' => 'https://dummyjson.com?page=2',
                        'total' => 2,
                    ],
                    'products' => [
                        [
                            'id' => 'e020b4af-aff6-427f-8b5d-ca41df9f57a5',
                            'title' => 'Test Product 2',
                            'slug' => 'test-product-2',
                            'price' => ['current' => 200],
                            'stock' => ['quantity' => 20, 'in_stock' => true],
                            'status' => 'published',
                        ],
                    ],
                ],
            ], 200),
    ]);

    artisan('products:import --resume')
        ->assertSuccessful()
        ->expectsOutputToContain(__('products.command.resuming_from', ['page' => 2]));

    expect(Product::count())->toBe(1)
        ->and(Product::first()->slug)->toBe('test-product-2');
});

it('validates products and skips invalid ones', function () {
    Http::fake([
        '*' => Http::response([
            'status' => ['code' => 200],
            'data' => [
                'pagination' => [
                    'current_page' => 1,
                    'last_page_url' => 'https://dummyjson.com?page=1',
                    'total' => 2,
                ],
                'products' => [
                    [
                        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
                        'title' => 'Valid Product',
                        'slug' => 'valid-product',
                        'price' => ['current' => 100],
                        'stock' => ['quantity' => 10, 'in_stock' => true],
                        'status' => 'published',
                    ],
                    [
                        'id' => 'invalid-id',
                        'title' => 'Invalid Product',
                        'slug' => 'invalid-product',
                        'price' => ['current' => -10],
                        'stock' => ['quantity' => 5, 'in_stock' => true],
                        'status' => 'published',
                    ],
                ],
            ],
        ], 200),
    ]);

    artisan('products:import')
        ->assertSuccessful();

    expect(Product::count())->toBe(1)
        ->and(Product::first()->slug)->toBe('valid-product');
});

it('upserts existing products', function () {
    Product::factory()->create([
        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
        'slug' => 'existing-product',
        'title' => 'Old Title',
        'price' => 100,
        'quantity' => 5,
    ]);

    Http::fake([
        '*' => Http::response([
            'status' => ['code' => 200],
            'data' => [
                'pagination' => [
                    'current_page' => 1,
                    'last_page_url' => 'https://dummyjson.com?page=1',
                    'total' => 1,
                ],
                'products' => [
                    [
                        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
                        'title' => 'New Title',
                        'slug' => 'existing-product',
                        'price' => ['current' => 200],
                        'stock' => ['quantity' => 15, 'in_stock' => true],
                        'status' => 'published',
                    ],
                ],
            ],
        ], 200),
    ]);

    artisan('products:import')
        ->assertSuccessful();

    $product = Product::where('slug', 'existing-product')->first();

    expect(Product::count())->toBe(1)
        ->and($product->title)->toBe('New Title')
        ->and($product->price)->toBe('200.00')
        ->and($product->quantity)->toBe(15);
});

it('displays summary report', function () {
    Http::fake([
        '*' => Http::response([
            'status' => ['code' => 200],
            'data' => [
                'pagination' => [
                    'current_page' => 1,
                    'last_page_url' => 'https://dummyjson.com?page=1',
                    'total' => 1,
                ],
                'products' => [
                    [
                        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
                        'title' => 'Test Product',
                        'slug' => 'test-product',
                        'price' => ['current' => 100],
                        'stock' => ['quantity' => 10, 'in_stock' => true],
                        'status' => 'published',
                    ],
                ],
            ],
        ], 200),
    ]);

    artisan('products:import')
        ->assertSuccessful()
        ->expectsOutputToContain(__('products.summary.title'))
        ->expectsOutputToContain(__('products.summary.total_processed'))
        ->expectsOutputToContain(__('products.summary.successful_imports'))
        ->expectsOutputToContain(__('products.summary.total_duration'));
});
