<?php

namespace Database\Factories;

use App\Enums\ReleaseReviewStatus;
use App\Models\Prisoner;
use App\Models\ReleaseReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReleaseReview>
 */
class ReleaseReviewFactory extends Factory
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
            'initiated_by' => User::factory(),
            'initiated_at' => now(),
            'status' => ReleaseReviewStatus::InProgress,
        ];
    }
}
