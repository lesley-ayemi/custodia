<?php

namespace Database\Factories;

use App\Enums\MedicalAppointmentStatus;
use App\Models\MedicalAppointment;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalAppointment>
 */
class MedicalAppointmentFactory extends Factory
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
            'scheduled_by' => User::factory(),
            'appointment_type' => fake()->randomElement(['GP review', 'Dental', 'Psychiatric assessment', 'Optician']),
            'provider' => fake()->name(),
            'location' => 'Health Wing',
            'scheduled_at' => now()->addWeek(),
            'status' => MedicalAppointmentStatus::Scheduled,
        ];
    }
}
