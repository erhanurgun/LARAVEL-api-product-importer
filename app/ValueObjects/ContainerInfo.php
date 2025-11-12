<?php

namespace App\ValueObjects;

final readonly class ContainerInfo
{
    /**
     * @param  array<string>  $types
     */
    public function __construct(
        public array $types,
        public ?string $size = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $types = $data['types'] ?? [];

        if (! is_array($types)) {
            $types = [];
        }

        return new self(
            types: $types,
            size: $data['size'] ?? null,
        );
    }

    public function isEmpty(): bool
    {
        return empty($this->types) && $this->size === null;
    }

    public function hasType(string $type): bool
    {
        return in_array($type, $this->types, true);
    }

    public function getTypesAsString(): ?string
    {
        if (empty($this->types)) {
            return null;
        }

        return implode(',', $this->types);
    }

    public function toArray(): array
    {
        return [
            'types' => $this->types,
            'size' => $this->size,
        ];
    }

    public function toDatabaseFormat(): array
    {
        return [
            'container_type' => $this->getTypesAsString(),
            'container_size' => $this->size,
        ];
    }
}
