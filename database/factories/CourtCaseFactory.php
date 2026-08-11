<?php

namespace Database\Factories;

use App\Enums\CourtCaseStatus;
use App\Models\CourtCase;
use App\Models\Prisoner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourtCase>
 */
class CourtCaseFactory extends Factory
{
    protected static int $sequence = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_number' => sprintf('CASE-%d-%04d', now()->year, static::$sequence++),
            'prisoner_id' => Prisoner::factory(),
            'legal_representative_id' => null,
            'court_name' => fake()->randomElement(['District Court 4', 'Crown Court', 'Magistrates Court']),
            'charge' => fake()->randomElement(['Assault', 'Theft', 'Fraud', 'Burglary']),
            'status' => CourtCaseStatus::Open,
            'opened_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
