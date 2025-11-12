<?php

namespace App\Services;

class ProductDataMapper
{
    /**
     * Map API product data to database format.
     *
     * @param  array<string, mixed>  $apiProduct
     * @return array<string, mixed>
     */
    public function mapToDatabaseFormat(array $apiProduct): array
    {
        return [
            'id' => $apiProduct['id'],
            'title' => $apiProduct['title'],
            'slug' => $apiProduct['slug'],
            'content' => $apiProduct['content'] ?? null,

            'price' => $this->extractPrice($apiProduct),
            'old_price' => $this->extractOldPrice($apiProduct),
            'discount_percentage' => $this->extractDiscountPercentage($apiProduct),

            'quantity' => $this->extractQuantity($apiProduct),
            'in_stock' => $this->extractInStock($apiProduct),

            'image_cover' => $this->extractImageCover($apiProduct),
            'image_thumbnail' => $this->extractImageThumbnail($apiProduct),

            'container_type' => $this->extractContainerType($apiProduct),
            'container_size' => $this->extractContainerSize($apiProduct),
            'production_year' => $apiProduct['production_year'] ?? null,
            'condition' => $apiProduct['condition'] ?? null,

            'location_city' => $this->extractLocationCity($apiProduct),
            'location_district' => $this->extractLocationDistrict($apiProduct),
            'location_country' => $this->extractLocationCountry($apiProduct),

            'type' => $apiProduct['type'] ?? null,
            'is_new' => $apiProduct['is_new'] ?? false,
            'is_hot_sale' => $apiProduct['is_hot_sale'] ?? false,
            'is_featured' => $apiProduct['is_featured'] ?? false,
            'is_bulk_sale' => $apiProduct['is_bulk_sale'] ?? false,
            'accept_offers' => $apiProduct['accept_offers'] ?? false,
            'status' => $apiProduct['status'] ?? 'draft',

            'colors' => $apiProduct['colors'] ?? null,
            'all_prices' => $apiProduct['all_prices'] ?? null,
            'technical_specs' => $apiProduct['technical_specs'] ?? null,
            'user_info' => $this->extractUserInfo($apiProduct),
        ];
    }

    private function extractPrice(array $product): float
    {
        return (float) ($product['price']['current'] ?? 0);
    }

    private function extractOldPrice(array $product): ?float
    {
        $oldPrice = $product['price']['old'] ?? null;

        return $oldPrice !== null ? (float) $oldPrice : null;
    }

    private function extractDiscountPercentage(array $product): ?int
    {
        $discount = $product['price']['discount'] ?? null;

        return $discount !== null ? (int) $discount : null;
    }

    private function extractQuantity(array $product): int
    {
        return (int) ($product['stock']['quantity'] ?? 0);
    }

    private function extractInStock(array $product): bool
    {
        return (bool) ($product['stock']['in_stock'] ?? false);
    }

    private function extractImageCover(array $product): ?string
    {
        return $product['image']['cover'] ?? null;
    }

    private function extractImageThumbnail(array $product): ?string
    {
        return $product['image']['thumbnail'] ?? null;
    }

    private function extractContainerType(array $product): ?string
    {
        $types = $product['container']['types'] ?? [];

        return is_array($types) && count($types) > 0 ? implode(',', $types) : null;
    }

    private function extractContainerSize(array $product): ?string
    {
        return $product['container']['size'] ?? null;
    }

    private function extractLocationCity(array $product): ?string
    {
        return $product['location']['city'] ?? null;
    }

    private function extractLocationDistrict(array $product): ?string
    {
        return $product['location']['district'] ?? null;
    }

    private function extractLocationCountry(array $product): ?string
    {
        return $product['location']['country'] ?? null;
    }

    private function extractUserInfo(array $product): ?array
    {
        return $product['user'] ?? null;
    }
}
