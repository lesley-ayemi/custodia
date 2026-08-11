<?php

namespace Database\Factories;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Enums\IncidentType;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
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
            'incident_number' => sprintf('INC-%d-%04d', now()->year, static::$sequence++),
            'officer_id' => User::factory(),
            'type' => fake()->randomElement(IncidentType::cases()),
            'severity' => fake()->randomElement(IncidentSeverity::cases()),
            'location' => fake()->randomElement(['Block A Yard', 'Cafeteria', 'Workshop', 'Medical Wing']),
            'description' => fake()->sentence(10),
            'occurred_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'status' => IncidentStatus::Reported,
        ];
    }
}
