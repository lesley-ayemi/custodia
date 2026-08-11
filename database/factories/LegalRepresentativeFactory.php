<?php

namespace Database\Factories;

use App\Models\LegalRepresentative;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegalRepresentative>
 */
class LegalRepresentativeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'firm_name' => fake()->company().' LLP',
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
