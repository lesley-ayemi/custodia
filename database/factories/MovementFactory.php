<?php

namespace Database\Factories;

use App\Enums\MovementStatus;
use App\Models\Movement;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movement>
 */
class MovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'prisoner_id' => Prisoner::factory(),
            'from_location' => 'HMP Custodia',
            'to_location' => fake()->randomElement(['Crown Court', 'City Hospital', 'HMP Otherplace']),
            'reason' => fake()->randomElement(['Court appearance', 'Medical appointment', 'Transfer']),
            'requested_by' => User::factory(),
            'scheduled_at' => now()->addDay(),
            'status' => MovementStatus::Requested,
        ];
    }
}
