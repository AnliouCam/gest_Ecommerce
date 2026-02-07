<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockAdjustment>
 */
class StockAdjustmentFactory extends Factory
{
    protected $model = StockAdjustment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['perte', 'casse', 'inventaire']);

        $reasons = [
            'perte' => ['Vol suspecte', 'Produit introuvable', 'Erreur de comptage'],
            'casse' => ['Produit endommage', 'Chute en entrepot', 'Defaut de fabrication'],
            'inventaire' => ['Correction inventaire', 'Ecart de stock', 'Regularisation'],
        ];

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory()->gerant(),
            'quantity' => fake()->numberBetween(-10, 10),
            'type' => $type,
            'reason' => fake()->randomElement($reasons[$type]),
        ];
    }

    /**
     * Loss adjustment.
     */
    public function perte(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'perte',
            'quantity' => -1 * fake()->numberBetween(1, 5),
            'reason' => fake()->randomElement(['Vol suspecte', 'Produit introuvable', 'Erreur de comptage']),
        ]);
    }

    /**
     * Breakage adjustment.
     */
    public function casse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'casse',
            'quantity' => -1 * fake()->numberBetween(1, 3),
            'reason' => fake()->randomElement(['Produit endommage', 'Chute en entrepot', 'Defaut de fabrication']),
        ]);
    }

    /**
     * Inventory adjustment.
     */
    public function inventaire(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'inventaire',
            'quantity' => fake()->numberBetween(-5, 5),
            'reason' => fake()->randomElement(['Correction inventaire', 'Ecart de stock', 'Regularisation']),
        ]);
    }
}
