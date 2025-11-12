<?php

namespace App\Enums;

use App\Traits\HasEnumHelpers;

enum ProductType: string
{
    use HasEnumHelpers;

    case SALE = 'sale';
    case RENT = 'rent';

    public function label(): string
    {
        return match ($this) {
            self::SALE => __('products.type.sale'),
            self::RENT => __('products.type.rent'),
        };
    }
}
