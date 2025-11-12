<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class Stock
{
    public function __construct(
        public int $quantity,
        public bool $inStock,
    ) {
        if ($this->quantity < 0) {
            throw new InvalidArgumentException('Quantity cannot be negative');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            quantity: (int) ($data['quantity'] ?? 0),
            inStock: (bool) ($data['in_stock'] ?? false),
        );
    }

    public function isAvailable(): bool
    {
        return $this->inStock && $this->quantity > 0;
    }

    public function isOutOfStock(): bool
    {
        return ! $this->inStock || $this->quantity === 0;
    }

    public function isLowStock(int $threshold = 5): bool
    {
        return $this->inStock && $this->quantity > 0 && $this->quantity <= $threshold;
    }

    public function toArray(): array
    {
        return [
            'quantity' => $this->quantity,
            'in_stock' => $this->inStock,
        ];
    }
}
