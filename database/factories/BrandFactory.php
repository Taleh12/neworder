<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph,
           // 'logo' => 'brands/' . $this->faker->image('public/storage/brands', 200, 200, null, false), // fake image path
            'website' => $this->faker->url,
            'contact_email' => $this->faker->companyEmail,
            'contact_phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'social_media' => json_encode([
                'facebook' => $this->faker->url,
                'instagram' => $this->faker->url,
                'linkedin' => $this->faker->url,
            ]),
            'is_active' => $this->faker->boolean(90), // 90% aktiv
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
