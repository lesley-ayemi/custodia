<?php

namespace Database\Factories;

use App\Enums\ReleaseStep;
use App\Models\ReleaseReview;
use App\Models\ReleaseReviewStep;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReleaseReviewStep>
 */
class ReleaseReviewStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'release_review_id' => ReleaseReview::factory(),
            'step' => ReleaseStep::LegalVerification,
            'completed_by' => User::factory(),
            'completed_at' => now(),
        ];
    }
}
