<?php

namespace Database\Factories;

use App\Enums\PrescriptionStatus;
use App\Models\Prescription;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
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
            'prescribed_by' => User::factory(),
            'medication_name' => fake()->randomElement(['Metformin', 'Salbutamol', 'Lisinopril', 'Paracetamol']),
            'dosage' => '500mg',
            'frequency' => 'Twice daily',
            'administration_time' => '14:00',
            'start_date' => now()->toDateString(),
            'status' => PrescriptionStatus::Active,
        ];
    }
}
