<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class Price
{
    public function __construct(
        public float $current,
        public ?float $old = null,
        public ?int $discountPercentage = null,
    ) {
        if ($this->current < 0) {
            throw new InvalidArgumentException('Price cannot be negative');
        }

        if ($this->old !== null && $this->old < 0) {
            throw new InvalidArgumentException('Old price cannot be negative');
        }

        if ($this->discountPercentage !== null && ($this->discountPercentage < 0 || $this->discountPercentage > 100)) {
            throw new InvalidArgumentException('Discount percentage must be between 0 and 100');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            current: (float) ($data['current'] ?? 0),
            old: isset($data['old']) ? (float) $data['old'] : null,
            discountPercentage: isset($data['discount']) ? (int) $data['discount'] : null,
        );
    }

    public function hasDiscount(): bool
    {
        return $this->old !== null && $this->old > $this->current;
    }

    public function calculateDiscount(): ?float
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return $this->old - $this->current;
    }

    public function calculateDiscountPercentage(): ?int
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return (int) round((($this->old - $this->current) / $this->old) * 100);
    }

    public function toArray(): array
    {
        return [
            'current' => $this->current,
            'old' => $this->old,
            'discount_percentage' => $this->discountPercentage ?? $this->calculateDiscountPercentage(),
        ];
    }
}
