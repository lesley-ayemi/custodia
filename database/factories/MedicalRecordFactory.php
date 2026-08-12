<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalRecord>
 */
class MedicalRecordFactory extends Factory
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
            'recorded_by' => User::factory(),
            'condition' => fake()->randomElement(['Asthma', 'Type 2 Diabetes', 'Hypertension', 'Seasonal allergy']),
            'notes' => fake()->sentence(),
            'recorded_at' => now(),
        ];
    }
}
