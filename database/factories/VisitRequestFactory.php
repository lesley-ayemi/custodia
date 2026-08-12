<?php

namespace Database\Factories;

use App\Enums\VisitRequestStatus;
use App\Models\Prisoner;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitRequest>
 */
class VisitRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'visitor_id' => Visitor::factory(),
            'prisoner_id' => Prisoner::factory(),
            'relationship' => fake()->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
            'requested_by' => User::factory(),
            'requested_visit_date' => now()->addWeek()->toDateString(),
            'status' => VisitRequestStatus::Pending,
        ];
    }
}
