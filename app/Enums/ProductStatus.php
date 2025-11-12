<?php

namespace App\Enums;

use App\Traits\HasEnumHelpers;

enum ProductStatus: string
{
    use HasEnumHelpers;

    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => __('products.status.draft'),
            self::PUBLISHED => __('products.status.published'),
            self::ARCHIVED => __('products.status.archived'),
        };
    }

    public function isPublished(): bool
    {
        return $this === self::PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isArchived(): bool
    {
        return $this === self::ARCHIVED;
    }
}
