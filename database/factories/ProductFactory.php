<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 100, 5000);
        $oldPrice = fake()->boolean(70) ? $price * 1.2 : null;

        return [
            'id' => fake()->uuid(),
            'title' => fake()->sentence(5),
            'slug' => fake()->unique()->slug(),
            'content' => fake()->paragraph(3),
            'price' => $price,
            'old_price' => $oldPrice,
            'discount_percentage' => $oldPrice ? (int) (($oldPrice - $price) / $oldPrice * 100) : null,
            'quantity' => fake()->numberBetween(0, 100),
            'in_stock' => fake()->boolean(80),
            'image_cover' => '/storage/upload/products/cover/'.fake()->uuid().'.webp',
            'image_thumbnail' => '/storage/upload/products/thumbnail/'.fake()->uuid().'.webp',
            'container_type' => fake()->randomElement(['high_cube', 'standard', 'open_top']),
            'container_size' => fake()->randomElement(['20HC', '40HC', '40ST', '45HC']),
            'production_year' => fake()->numberBetween(2020, 2025),
            'condition' => fake()->randomElement(['new', 'used', 'refurbished']),
            'location_city' => fake()->city(),
            'location_district' => fake()->word(),
            'location_country' => fake()->country(),
            'type' => fake()->randomElement(['sale', 'rent']),
            'is_new' => fake()->boolean(30),
            'is_hot_sale' => fake()->boolean(20),
            'is_featured' => fake()->boolean(15),
            'is_bulk_sale' => fake()->boolean(40),
            'accept_offers' => fake()->boolean(60),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'colors' => fake()->randomElements(['red', 'blue', 'green', 'yellow', 'white'], fake()->numberBetween(1, 3)),
            'all_prices' => [
                ['currency' => 'USD', 'price' => $price, 'symbol' => '$'],
                ['currency' => 'EUR', 'price' => $price * 0.92, 'symbol' => '€'],
            ],
            'technical_specs' => [
                ['key' => 'Weight', 'value' => fake()->numberBetween(2000, 5000).' kg'],
                ['key' => 'Volume', 'value' => fake()->numberBetween(30, 80).' m³'],
            ],
            'user_info' => [
                'id' => fake()->uuid(),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->email(),
            ],
        ];
    }
}
