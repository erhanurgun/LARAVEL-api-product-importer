<?php

namespace App\DataTransferObjects;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class ProductData extends Data
{
    public function __construct(
        public string $id,
        public string $title,
        public string $slug,
        public ?string $content,
        public float $price,
        public ?float $oldPrice,
        public ?int $discountPercentage,
        public int $quantity,
        public bool $inStock,
        public ?string $imageCover,
        public ?string $imageThumbnail,
        public ?string $containerType,
        public ?string $containerSize,
        public ?int $productionYear,
        public ?ProductCondition $condition,
        public ?string $locationCity,
        public ?string $locationDistrict,
        public ?string $locationCountry,
        public ?ProductType $type,
        public bool $isNew,
        public bool $isHotSale,
        public bool $isFeatured,
        public bool $isBulkSale,
        public bool $acceptOffers,
        public ProductStatus $status,
        public ?array $colors,
        public ?array $allPrices,
        public ?array $technicalSpecs,
        public ?array $userInfo,
    ) {}

    /**
     * Create ProductData from API response format.
     */
    public static function fromApiFormat(array $apiProduct): self
    {
        return new self(
            id: $apiProduct['id'],
            title: $apiProduct['title'],
            slug: $apiProduct['slug'],
            content: $apiProduct['content'] ?? null,
            price: (float) ($apiProduct['price']['current'] ?? 0),
            oldPrice: isset($apiProduct['price']['old']) ? (float) $apiProduct['price']['old'] : null,
            discountPercentage: isset($apiProduct['price']['discount']) ? (int) $apiProduct['price']['discount'] : null,
            quantity: (int) ($apiProduct['stock']['quantity'] ?? 0),
            inStock: (bool) ($apiProduct['stock']['in_stock'] ?? false),
            imageCover: $apiProduct['image']['cover'] ?? null,
            imageThumbnail: $apiProduct['image']['thumbnail'] ?? null,
            containerType: self::extractContainerType($apiProduct),
            containerSize: $apiProduct['container']['size'] ?? null,
            productionYear: $apiProduct['production_year'] ?? null,
            condition: isset($apiProduct['condition']) ? ProductCondition::from($apiProduct['condition']) : null,
            locationCity: $apiProduct['location']['city'] ?? null,
            locationDistrict: $apiProduct['location']['district'] ?? null,
            locationCountry: $apiProduct['location']['country'] ?? null,
            type: isset($apiProduct['type']) ? ProductType::from($apiProduct['type']) : null,
            isNew: $apiProduct['is_new'] ?? false,
            isHotSale: $apiProduct['is_hot_sale'] ?? false,
            isFeatured: $apiProduct['is_featured'] ?? false,
            isBulkSale: $apiProduct['is_bulk_sale'] ?? false,
            acceptOffers: $apiProduct['accept_offers'] ?? false,
            status: isset($apiProduct['status']) ? ProductStatus::from($apiProduct['status']) : ProductStatus::DRAFT,
            colors: $apiProduct['colors'] ?? null,
            allPrices: $apiProduct['all_prices'] ?? null,
            technicalSpecs: $apiProduct['technical_specs'] ?? null,
            userInfo: $apiProduct['user'] ?? null,
        );
    }

    /**
     * Extract container type from API data.
     */
    private static function extractContainerType(array $product): ?string
    {
        $types = $product['container']['types'] ?? [];

        return is_array($types) && count($types) > 0 ? implode(',', $types) : null;
    }
}
