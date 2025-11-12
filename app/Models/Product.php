<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'title',
        'slug',
        'content',
        'price',
        'old_price',
        'discount_percentage',
        'quantity',
        'in_stock',
        'image_cover',
        'image_thumbnail',
        'container_type',
        'container_size',
        'production_year',
        'condition',
        'location_city',
        'location_district',
        'location_country',
        'type',
        'is_new',
        'is_hot_sale',
        'is_featured',
        'is_bulk_sale',
        'accept_offers',
        'status',
        'colors',
        'all_prices',
        'technical_specs',
        'user_info',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'discount_percentage' => 'integer',
            'quantity' => 'integer',
            'in_stock' => 'boolean',
            'production_year' => 'integer',
            'is_new' => 'boolean',
            'is_hot_sale' => 'boolean',
            'is_featured' => 'boolean',
            'is_bulk_sale' => 'boolean',
            'accept_offers' => 'boolean',
            'colors' => 'array',
            'all_prices' => 'array',
            'technical_specs' => 'array',
            'user_info' => 'array',
        ];
    }
}
