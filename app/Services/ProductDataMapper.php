<?php

namespace App\Services;

use App\Contracts\DataMapperInterface;
use App\DataTransferObjects\ProductData;

final class ProductDataMapper implements DataMapperInterface
{
    /**
     * Map API product data to database format.
     *
     * This method now uses ProductData DTO for type-safe mapping and validation.
     *
     * @param  array<string, mixed>  $apiProduct
     * @return array<string, mixed>
     */
    public function mapToDatabaseFormat(array $apiProduct): array
    {
        $productData = ProductData::fromApiFormat($apiProduct);

        return $productData->toArray();
    }
}
