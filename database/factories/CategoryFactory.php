<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Beverages', 'Dairy & Eggs', 'Snacks & Confectionery',
            'Bakery & Bread', 'Fruits & Vegetables', 'Meat & Seafood',
            'Frozen Foods', 'Condiments & Sauces', 'Breakfast & Cereals',
            'Personal Care', 'Household Cleaning', 'Baby Products',
            'Health & Wellness', 'Canned & Jarred Goods', 'Pasta & Rice',
            'Cooking Oils & Vinegars', 'Spices & Herbs', 'Beverages & Juices',
            'Pet Food', 'Stationery & Office Supplies',
        ];

        return [
            'name'        => fake()->unique()->randomElement($categories),
            'description' => fake()->optional(0.8)->sentence(10),
        ];
    }
}
