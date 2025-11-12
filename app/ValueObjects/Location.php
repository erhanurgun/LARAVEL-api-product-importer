<?php

namespace App\ValueObjects;

final readonly class Location
{
    public function __construct(
        public ?string $city = null,
        public ?string $district = null,
        public ?string $country = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            city: $data['city'] ?? null,
            district: $data['district'] ?? null,
            country: $data['country'] ?? null,
        );
    }

    public function isEmpty(): bool
    {
        return $this->city === null
            && $this->district === null
            && $this->country === null;
    }

    public function isComplete(): bool
    {
        return $this->city !== null
            && $this->district !== null
            && $this->country !== null;
    }

    public function getFullAddress(): ?string
    {
        if ($this->isEmpty()) {
            return null;
        }

        return implode(', ', array_filter([
            $this->district,
            $this->city,
            $this->country,
        ]));
    }

    public function toArray(): array
    {
        return [
            'city' => $this->city,
            'district' => $this->district,
            'country' => $this->country,
        ];
    }
}
