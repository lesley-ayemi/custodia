<?php

namespace Database\Seeders;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentType;
use App\Enums\Role;
use App\Models\Prisoner;
use App\Models\User;
use App\Services\IncidentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class IncidentSeeder extends Seeder
{
    /**
     * Target statuses for the seeded incidents, in creation order.
     * 7 open (reported + under_review) + 5 resolved, matching the demo dashboard.
     *
     * @var list<'reported'|'under_review'|'resolved'>
     */
    protected array $statuses = [
        'reported', 'reported', 'reported', 'reported',
        'under_review', 'under_review', 'under_review',
        'resolved', 'resolved', 'resolved', 'resolved', 'resolved',
    ];

    protected array $locations = [
        'Block A Yard', 'Block B Cell A-104', 'Cafeteria', 'Visitation Room',
        'Block C Corridor', 'Medical Wing', 'Workshop', 'Block A Cell A-107',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $officer = User::where('role', Role::Officer)->firstOrFail();
        $supervisor = User::where('role', Role::Supervisor)->firstOrFail();
        $prisoners = Prisoner::inRandomOrder()->limit(count($this->statuses))->get();
        $incidents = app(IncidentService::class);

        // Shuffle so occurred_at (chronological) doesn't correlate with status,
        // giving the dashboard's "recent incidents" a realistic mix of statuses.
        $statuses = collect($this->statuses)->shuffle()->all();

        foreach ($statuses as $index => $status) {
            $prisoner = $prisoners[$index % $prisoners->count()];
            $occurredAt = Carbon::now()->subDays(count($this->statuses) - $index)->subHours(random_int(1, 12));

            $incident = $incidents->create([
                'prisoner_id' => $prisoner->id,
                'officer_id' => $officer->id,
                'type' => fake()->randomElement(IncidentType::cases()),
                'severity' => fake()->randomElement(IncidentSeverity::cases()),
                'location' => fake()->randomElement($this->locations),
                'description' => fake()->sentence(12),
                'occurred_at' => $occurredAt,
            ], $officer);

            if ($status === 'under_review' || $status === 'resolved') {
                $incidents->markUnderReview($incident, $supervisor);
            }

            if ($status === 'resolved') {
                $incidents->resolve($incident, $supervisor);
            }
        }
    }
}
