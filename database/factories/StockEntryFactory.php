<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockEntry>
 */
class StockEntryFactory extends Factory
{
    protected $model = StockEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(5, 50),
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Large stock entry.
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(100, 500),
        ]);
    }

    /**
     * Recent stock entry.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
