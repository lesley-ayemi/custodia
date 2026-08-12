<?php

namespace Database\Factories;

use App\Enums\VisitStatus;
use App\Models\Visit;
use App\Models\VisitRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $visitRequest = VisitRequest::factory()->create();

        return [
            'visit_request_id' => $visitRequest->id,
            'prisoner_id' => $visitRequest->prisoner_id,
            'visitor_id' => $visitRequest->visitor_id,
            'scheduled_at' => now()->addWeek(),
            'status' => VisitStatus::Scheduled,
        ];
    }
}
