<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchasePrice = fake()->randomFloat(2, 50000, 500000);
        $margin = fake()->randomFloat(2, 1.1, 1.4);

        return [
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'category_id' => Category::factory(),
            'purchase_price' => $purchasePrice,
            'sale_price' => round($purchasePrice * $margin, 2),
            'quantity' => fake()->numberBetween(0, 100),
            'image' => null,
            'max_discount' => fake()->randomElement([0, 5, 10, 15, 20]),
            'stock_alert' => fake()->numberBetween(3, 10),
        ];
    }

    /**
     * Product with low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(0, 3),
            'stock_alert' => 5,
        ]);
    }

    /**
     * Product out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }

    /**
     * Expensive product.
     */
    public function expensive(): static
    {
        return $this->state(function (array $attributes) {
            $purchasePrice = fake()->randomFloat(2, 500000, 2000000);
            return [
                'purchase_price' => $purchasePrice,
                'sale_price' => round($purchasePrice * 1.25, 2),
            ];
        });
    }
}
