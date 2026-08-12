<?php

namespace Database\Factories;

use App\Enums\MedicalAlertSeverity;
use App\Models\MedicalAlert;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalAlert>
 */
class MedicalAlertFactory extends Factory
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
            'created_by' => User::factory(),
            'message' => 'Requires medication at 14:00',
            'severity' => MedicalAlertSeverity::Medium,
            'active' => true,
        ];
    }
}
