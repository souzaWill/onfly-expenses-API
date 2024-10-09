<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->word(),
            'date' => now()->format('Y-m-d'),
            'user_id' => User::factory(),
            'value' => fake()->randomFloat(2, 0.01, 99999999.99), // Gera valores de 0.01 a 99,999,999.99
        ];
    }
}
