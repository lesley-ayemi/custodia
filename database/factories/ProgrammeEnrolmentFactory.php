<?php

namespace Database\Factories;

use App\Enums\EnrolmentStatus;
use App\Models\Prisoner;
use App\Models\Programme;
use App\Models\ProgrammeEnrolment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgrammeEnrolment>
 */
class ProgrammeEnrolmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'programme_id' => Programme::factory(),
            'prisoner_id' => Prisoner::factory(),
            'enrolled_by' => User::factory(),
            'enrolled_at' => now(),
            'status' => EnrolmentStatus::Enrolled,
        ];
    }
}
