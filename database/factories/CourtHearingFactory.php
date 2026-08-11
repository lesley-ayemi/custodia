<?php

namespace Database\Factories;

use App\Enums\HearingStatus;
use App\Enums\HearingType;
use App\Models\CourtCase;
use App\Models\CourtHearing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourtHearing>
 */
class CourtHearingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'court_case_id' => CourtCase::factory(),
            'type' => fake()->randomElement(HearingType::cases()),
            'scheduled_at' => fake()->dateTimeBetween('now', '+2 months'),
            'location' => fake()->randomElement(['Courtroom 1', 'Courtroom 2', 'Courtroom 3']),
            'status' => HearingStatus::Scheduled,
        ];
    }
}
