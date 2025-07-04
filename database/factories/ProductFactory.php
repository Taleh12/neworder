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
       return [
            'name' => $this->faker->words(3, true), // Məsələn: "Super Wireless Headphones"
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 1000), // 10.00 - 1000.00 AZN
            'stock' => $this->faker->numberBetween(0, 500),
            //'image' => 'products/' . $this->faker->image('public/storage/products', 400, 400, null, false),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}