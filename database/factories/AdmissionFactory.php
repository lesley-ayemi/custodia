<?php

namespace Database\Factories;

use App\Enums\AdmissionStatus;
use App\Models\Admission;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admission>
 */
class AdmissionFactory extends Factory
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
            'admitted_by' => User::factory(),
            'admission_date' => now()->toDateString(),
            'admission_reason' => fake()->randomElement(['Remanded in custody', 'Sentenced', 'Recall to custody']),
            'status' => AdmissionStatus::Draft,
        ];
    }
}
