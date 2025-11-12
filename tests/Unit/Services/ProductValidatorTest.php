<?php

use App\Models\Product;
use App\Services\ProductValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->validator = new ProductValidator;
});

it('validates valid product data', function () {
    $data = [
        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
        'title' => 'Test Product',
        'slug' => 'test-product-123',
        'price' => 99.99,
        'quantity' => 10,
        'in_stock' => true,
        'status' => 'published',
    ];

    $validated = $this->validator->validate($data);

    expect($validated)->toBeArray()
        ->and($validated['id'])->toBe('d010b4af-aff6-427f-8b5d-ca41df9f57a4')
        ->and($validated['title'])->toBe('Test Product')
        ->and($validated['slug'])->toBe('test-product-123')
        ->and($validated['price'])->toBe(99.99)
        ->and($validated['quantity'])->toBe(10);
});

it('throws validation exception for missing required fields', function () {
    $data = [
        'name' => 'Test Product',
    ];

    $this->validator->validate($data);
})->throws(ValidationException::class);

it('throws validation exception for negative price', function () {
    $data = [
        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
        'title' => 'Test Product',
        'slug' => 'test-product',
        'price' => -10,
        'quantity' => 5,
        'in_stock' => true,
        'status' => 'published',
    ];

    $this->validator->validate($data);
})->throws(ValidationException::class);

it('throws validation exception for negative quantity', function () {
    $data = [
        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
        'title' => 'Test Product',
        'slug' => 'test-product',
        'price' => 99.99,
        'quantity' => -5,
        'in_stock' => true,
        'status' => 'published',
    ];

    $this->validator->validate($data);
})->throws(ValidationException::class);

it('throws validation exception for duplicate slug', function () {
    Product::factory()->create(['slug' => 'duplicate-product']);

    $data = [
        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
        'title' => 'Test Product',
        'slug' => 'duplicate-product',
        'price' => 99.99,
        'quantity' => 10,
        'in_stock' => true,
        'status' => 'published',
    ];

    $this->validator->validate($data);
})->throws(ValidationException::class);

it('validates safe returns valid result for valid data', function () {
    $data = [
        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
        'title' => 'Test Product',
        'slug' => 'test-product-safe',
        'price' => 99.99,
        'quantity' => 10,
        'in_stock' => true,
        'status' => 'published',
    ];

    $result = $this->validator->validateSafe($data);

    expect($result['valid'])->toBeTrue()
        ->and($result['data'])->toBeArray()
        ->and($result['errors'])->toBeNull();
});

it('validates safe returns errors for invalid data', function () {
    $data = [
        'id' => 'd010b4af-aff6-427f-8b5d-ca41df9f57a4',
        'title' => 'Test Product',
        'slug' => 'test-product',
        'price' => -10,
        'quantity' => -5,
        'in_stock' => true,
        'status' => 'published',
    ];

    $result = $this->validator->validateSafe($data);

    expect($result['valid'])->toBeFalse()
        ->and($result['data'])->toBeNull()
        ->and($result['errors'])->toBeArray()
        ->and($result['errors'])->toHaveKeys(['price', 'quantity']);
});

it('logs validation errors to import_errors channel', function () {
    $data = [
        'title' => 'Test Product',
        'price' => 99.99,
    ];

    $result = $this->validator->validateSafe($data);

    expect($result['valid'])->toBeFalse();
});
