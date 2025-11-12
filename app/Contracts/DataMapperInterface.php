<?php

namespace App\Contracts;

interface DataMapperInterface
{
    /**
     * Map API product data to database format.
     *
     * @param  array<string, mixed>  $apiProduct
     * @return array<string, mixed>
     */
    public function mapToDatabaseFormat(array $apiProduct): array;
}
