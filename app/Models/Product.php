<?php

namespace App\Models;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
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
            'status' => ProductStatus::class,
            'type' => ProductType::class,
            'condition' => ProductCondition::class,
            'colors' => 'array',
            'all_prices' => 'array',
            'technical_specs' => 'array',
            'user_info' => 'array',
        ];
    }

    /**
     * Bootstrap model.
     */
    protected static function booted(): void
    {
        self::addGlobalScope('excludeArchived', function (Builder $builder) {
            $builder->where('status', '!=', ProductStatus::ARCHIVED->value);
        });
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

    /**
     * Scope: Include archived products (disable global scope).
     */
    public function scopeWithArchived(Builder $query): Builder
    {
        return $query->withoutGlobalScope('excludeArchived');
    }

    /**
     * Scope: Only archived products.
     */
    public function scopeOnlyArchived(Builder $query): Builder
    {
        return $query->withoutGlobalScope('excludeArchived')
            ->where('status', ProductStatus::ARCHIVED);
    }

    /**
     * Accessor: Get formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2).' TRY';
    }

    /**
     * Accessor: Check if product has discount.
     */
    public function getHasDiscountAttribute(): bool
    {
        return $this->old_price !== null && $this->old_price > $this->price;
    }

    /**
     * Accessor: Get discount amount.
     */
    public function getDiscountAmountAttribute(): ?float
    {
        if (! $this->has_discount) {
            return null;
        }

        return $this->old_price - $this->price;
    }

    /**
     * Accessor: Get calculated discount percentage.
     */
    public function getCalculatedDiscountPercentageAttribute(): ?int
    {
        if (! $this->has_discount) {
            return null;
        }

        return (int) round((($this->old_price - $this->price) / $this->old_price) * 100);
    }
}
