<?php

use App\Models\Product;
use App\Presenters\ProductPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('formats price correctly', function () {
    $product = Product::factory()->create(['price' => 1234.56]);
    $presenter = new ProductPresenter($product);

    expect($presenter->formattedPrice())->toBe('1,234.56 TRY');
});

it('detects products with discount', function () {
    $product = Product::factory()->create([
        'price' => 100.00,
        'old_price' => 150.00,
    ]);
    $presenter = new ProductPresenter($product);

    expect($presenter->hasDiscount())->toBeTrue();
});

it('detects products without discount', function () {
    $product = Product::factory()->create([
        'price' => 100.00,
        'old_price' => null,
    ]);
    $presenter = new ProductPresenter($product);

    expect($presenter->hasDiscount())->toBeFalse();
});

it('calculates discount amount', function () {
    $product = Product::factory()->create([
        'price' => 100.00,
        'old_price' => 150.00,
    ]);
    $presenter = new ProductPresenter($product);

    expect($presenter->discountAmount())->toBe(50.0);
});

it('returns null discount amount when no discount', function () {
    $product = Product::factory()->create([
        'price' => 100.00,
        'old_price' => null,
    ]);
    $presenter = new ProductPresenter($product);

    expect($presenter->discountAmount())->toBeNull();
});

it('calculates discount percentage', function () {
    $product = Product::factory()->create([
        'price' => 75.00,
        'old_price' => 100.00,
    ]);
    $presenter = new ProductPresenter($product);

    expect($presenter->calculatedDiscountPercentage())->toBe(25);
});

it('formats old price correctly', function () {
    $product = Product::factory()->create(['old_price' => 999.99]);
    $presenter = new ProductPresenter($product);

    expect($presenter->formattedOldPrice())->toBe('999.99 TRY');
});

it('returns null for formatted old price when not set', function () {
    $product = Product::factory()->create(['old_price' => null]);
    $presenter = new ProductPresenter($product);

    expect($presenter->formattedOldPrice())->toBeNull();
});

it('generates discount badge text', function () {
    $product = Product::factory()->create([
        'price' => 50.00,
        'old_price' => 100.00,
    ]);
    $presenter = new ProductPresenter($product);

    expect($presenter->discountBadge())->toBe('-50%');
});

it('returns null discount badge when no discount', function () {
    $product = Product::factory()->create([
        'price' => 100.00,
        'old_price' => null,
    ]);
    $presenter = new ProductPresenter($product);

    expect($presenter->discountBadge())->toBeNull();
});
