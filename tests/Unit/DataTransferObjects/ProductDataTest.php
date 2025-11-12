<?php

use App\DataTransferObjects\ProductData;
use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\ProductType;

it('creates product data from api format', function () {
    $apiProduct = [
        'id' => 'uuid-123',
        'title' => 'Test Product',
        'slug' => 'test-product',
        'content' => 'Product description',
        'price' => ['current' => 100.50, 'old' => 150.00, 'discount' => 33],
        'stock' => ['quantity' => 10, 'in_stock' => true],
        'image' => ['cover' => 'cover.jpg', 'thumbnail' => 'thumb.jpg'],
        'container' => ['types' => ['type1', 'type2'], 'size' => 'large'],
        'production_year' => 2024,
        'condition' => 'new',
        'location' => ['city' => 'Istanbul', 'district' => 'Kadikoy', 'country' => 'Turkey'],
        'type' => 'sale',
        'is_new' => true,
        'is_hot_sale' => false,
        'is_featured' => true,
        'is_bulk_sale' => false,
        'accept_offers' => true,
        'status' => 'published',
        'colors' => ['red', 'blue'],
        'all_prices' => ['USD' => 100, 'EUR' => 90],
        'technical_specs' => ['weight' => '1kg'],
        'user' => ['name' => 'John Doe'],
    ];

    $productData = ProductData::fromApiFormat($apiProduct);

    expect($productData->id)->toBe('uuid-123')
        ->and($productData->title)->toBe('Test Product')
        ->and($productData->price)->toBe(100.50)
        ->and($productData->oldPrice)->toBe(150.00)
        ->and($productData->discountPercentage)->toBe(33)
        ->and($productData->quantity)->toBe(10)
        ->and($productData->inStock)->toBeTrue()
        ->and($productData->condition)->toBe(ProductCondition::NEW)
        ->and($productData->type)->toBe(ProductType::SALE)
        ->and($productData->status)->toBe(ProductStatus::PUBLISHED)
        ->and($productData->containerType)->toBe('type1,type2');
});

it('handles missing optional fields', function () {
    $apiProduct = [
        'id' => 'uuid-123',
        'title' => 'Test Product',
        'slug' => 'test-product',
        'price' => ['current' => 100],
        'stock' => [],
    ];

    $productData = ProductData::fromApiFormat($apiProduct);

    expect($productData->id)->toBe('uuid-123')
        ->and($productData->content)->toBeNull()
        ->and($productData->oldPrice)->toBeNull()
        ->and($productData->condition)->toBeNull()
        ->and($productData->type)->toBeNull()
        ->and($productData->status)->toBe(ProductStatus::DRAFT);
});

it('converts to array with snake_case keys', function () {
    $apiProduct = [
        'id' => 'uuid-123',
        'title' => 'Test',
        'slug' => 'test',
        'price' => ['current' => 100],
        'stock' => ['quantity' => 5, 'in_stock' => true],
        'status' => 'published',
    ];

    $productData = ProductData::fromApiFormat($apiProduct);
    $array = $productData->toArray();

    expect($array)->toHaveKey('old_price')
        ->and($array)->toHaveKey('discount_percentage')
        ->and($array)->toHaveKey('in_stock')
        ->and($array)->toHaveKey('image_cover');
});
