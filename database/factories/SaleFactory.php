<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_id' => fake()->optional(0.7)->passthrough(Customer::factory()),
            'total' => fake()->randomFloat(2, 10000, 500000),
            'discount' => fake()->randomFloat(2, 0, 10000),
            'payment_method' => fake()->randomElement(['especes', 'mobile_money', 'carte']),
            'status' => 'completed',
            'cancelled_by' => null,
            'cancelled_at' => null,
            'cancel_reason' => null,
        ];
    }

    /**
     * Sale with customer.
     */
    public function withCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => Customer::factory(),
        ]);
    }

    /**
     * Anonymous sale (no customer).
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => null,
        ]);
    }

    /**
     * Cancelled sale.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_by' => User::factory()->gerant(),
            'cancelled_at' => now(),
            'cancel_reason' => fake()->randomElement([
                'Erreur de saisie',
                'Client a change d\'avis',
                'Produit defectueux',
                'Double saisie',
            ]),
        ]);
    }

    /**
     * Pending sale.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Cash payment.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'especes',
        ]);
    }

    /**
     * Mobile money payment.
     */
    public function mobileMoney(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'mobile_money',
        ]);
    }
}
