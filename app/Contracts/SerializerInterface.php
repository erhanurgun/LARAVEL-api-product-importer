<?php

namespace App\Contracts;

interface SerializerInterface
{
    /**
     * Serialize a single product for database storage.
     *
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    public function serialize(array $product): array;

    /**
     * Serialize multiple products for database storage.
     *
     * @param  array<array<string, mixed>>  $products
     * @return array<array<string, mixed>>
     */
    public function serializeBatch(array $products): array;
}
