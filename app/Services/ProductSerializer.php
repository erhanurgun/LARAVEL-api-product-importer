<?php

namespace App\Services;

use App\Contracts\SerializerInterface;

final class ProductSerializer implements SerializerInterface
{
    /**
     * Fields that should be JSON encoded for database storage.
     *
     * @var array<string>
     */
    private const JSON_FIELDS = [
        'colors',
        'all_prices',
        'technical_specs',
        'user_info',
    ];

    public function serialize(array $product): array
    {
        foreach (self::JSON_FIELDS as $field) {
            if (isset($product[$field]) && is_array($product[$field])) {
                $product[$field] = json_encode($product[$field]);
            }
        }

        return $product;
    }

    public function serializeBatch(array $products): array
    {
        return array_map(
            fn (array $product) => $this->serialize($product),
            $products
        );
    }
}
