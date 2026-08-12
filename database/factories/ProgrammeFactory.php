<?php

namespace Database\Factories;

use App\Enums\ProgrammeCategory;
use App\Enums\ProgrammeStatus;
use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Programme>
 */
class ProgrammeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Education', 'Counselling', 'Vocational Training', 'Substance Misuse Programme', 'Employment Training', 'Life Skills']),
            'category' => fake()->randomElement(ProgrammeCategory::cases()),
            'description' => fake()->sentence(),
            'capacity' => fake()->numberBetween(8, 20),
            'status' => ProgrammeStatus::Active,
        ];
    }
}
