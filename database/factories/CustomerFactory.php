<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional(0.6)->safeEmail(),
            'address' => fake()->optional(0.5)->address(),
        ];
    }

    /**
     * Customer with full contact info.
     */
    public function withFullContact(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
        ]);
    }

    /**
     * Business customer (enterprise).
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Entreprise ' . fake()->company(),
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
        ]);
    }
}
