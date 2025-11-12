<?php

namespace App\ValueObjects;

final readonly class ProductImage
{
    public function __construct(
        public ?string $cover = null,
        public ?string $thumbnail = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cover: $data['cover'] ?? null,
            thumbnail: $data['thumbnail'] ?? null,
        );
    }

    public function hasCover(): bool
    {
        return $this->cover !== null && $this->cover !== '';
    }

    public function hasThumbnail(): bool
    {
        return $this->thumbnail !== null && $this->thumbnail !== '';
    }

    public function hasAnyImage(): bool
    {
        return $this->hasCover() || $this->hasThumbnail();
    }

    public function toArray(): array
    {
        return [
            'cover' => $this->cover,
            'thumbnail' => $this->thumbnail,
        ];
    }
}
