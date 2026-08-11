<?php

namespace App\Services;

use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IncidentService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Incident
    {
        $data['incident_number'] = $this->nextIncidentNumber();
        $data['status'] ??= IncidentStatus::Reported;

        return Incident::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Incident $incident, array $data): Incident
    {
        $incident->update($data);

        return $incident;
    }

    public function delete(Incident $incident): void
    {
        $incident->delete();
    }

    public function markUnderReview(Incident $incident): Incident
    {
        $incident->status = IncidentStatus::UnderReview;
        $incident->save();

        return $incident;
    }

    public function resolve(Incident $incident, User $resolvedBy): Incident
    {
        $incident->status = IncidentStatus::Resolved;
        $incident->resolved_by = $resolvedBy->id;
        $incident->resolved_at = now();
        $incident->save();

        return $incident;
    }

    protected function nextIncidentNumber(): string
    {
        $year = Carbon::now()->year;

        $sequence = DB::table('incidents')
            ->where('incident_number', 'like', "INC-{$year}-%")
            ->count() + 1;

        return sprintf('INC-%d-%04d', $year, $sequence);
    }
}
