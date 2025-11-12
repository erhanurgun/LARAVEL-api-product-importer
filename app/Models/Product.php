<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'discount_percentage' => 'integer',
            'quantity' => 'integer',
            'in_stock' => 'boolean',
            'production_year' => 'integer',
            'is_new' => 'boolean',
            'is_hot_sale' => 'boolean',
            'is_featured' => 'boolean',
            'is_bulk_sale' => 'boolean',
            'accept_offers' => 'boolean',
            'colors' => 'array',
            'all_prices' => 'array',
            'technical_specs' => 'array',
            'user_info' => 'array',
        ];
    }

    /**
     * Scope: Published products.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: In stock products.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('in_stock', true)->where('quantity', '>', 0);
    }

    /**
     * Scope: Featured products.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Hot sale products.
     */
    public function scopeHotSale(Builder $query): Builder
    {
        return $query->where('is_hot_sale', true);
    }

    /**
     * Scope: Products with discount.
     */
    public function scopeWithDiscount(Builder $query): Builder
    {
        return $query->whereNotNull('old_price')
            ->whereColumn('old_price', '>', 'price');
    }
}
