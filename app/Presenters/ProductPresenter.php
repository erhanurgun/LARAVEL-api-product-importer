<?php

namespace App\Presenters;

use App\Models\Product;

final class ProductPresenter
{
    public function __construct(
        private readonly Product $product
    ) {}

    /**
     * Get formatted price with currency.
     */
    public function formattedPrice(): string
    {
        return number_format($this->product->price, 2).' TRY';
    }

    /**
     * Check if product has discount.
     */
    public function hasDiscount(): bool
    {
        return $this->product->old_price !== null && $this->product->old_price > $this->product->price;
    }

    /**
     * Get discount amount.
     */
    public function discountAmount(): ?float
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return $this->product->old_price - $this->product->price;
    }

    /**
     * Get calculated discount percentage.
     */
    public function calculatedDiscountPercentage(): ?int
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return (int) round((($this->product->old_price - $this->product->price) / $this->product->old_price) * 100);
    }

    /**
     * Get formatted old price with currency.
     */
    public function formattedOldPrice(): ?string
    {
        if ($this->product->old_price === null) {
            return null;
        }

        return number_format($this->product->old_price, 2).' TRY';
    }

    /**
     * Get discount badge text.
     */
    public function discountBadge(): ?string
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        $percentage = $this->calculatedDiscountPercentage();

        return $percentage !== null ? "-{$percentage}%" : null;
    }
}
