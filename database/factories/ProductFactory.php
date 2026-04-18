<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $buyPrice  = fake()->numberBetween(1000, 100000);
        // Sell price is always higher than buy price (10% – 50% margin)
        $sellPrice = (int) ($buyPrice * fake()->randomFloat(2, 1.10, 1.50));

        return [
            'category_id' => Category::factory(),
            'sku'         => strtoupper(Str::random(3)) . '-' . fake()->unique()->numerify('######'),
            'name'        => fake()->words(fake()->numberBetween(2, 4), true),
            'buy_price'   => $buyPrice,
            'sell_price'  => $sellPrice,
            'stock_qty'   => fake()->numberBetween(0, 500),
            'min_stock'   => fake()->numberBetween(5, 50),
            'status'      => fake()->randomElement(['active', 'inactive']),
        ];
    }

    /**
     * State: product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * State: product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
