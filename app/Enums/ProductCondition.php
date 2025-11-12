<?php

namespace App\Enums;

use App\Traits\HasEnumHelpers;

enum ProductCondition: string
{
    use HasEnumHelpers;

    case NEW = 'new';
    case USED = 'used';
    case REFURBISHED = 'refurbished';

    public function label(): string
    {
        return match ($this) {
            self::NEW => __('products.condition.new'),
            self::USED => __('products.condition.used'),
            self::REFURBISHED => __('products.condition.refurbished'),
        };
    }
}
