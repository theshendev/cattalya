<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'weight_grams' => fake()->randomFloat(2, 1, 10),
            'stock' => fake()->numberBetween(1, 5),
            'sort_order' => 0,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
