<?php

namespace App\Services;

use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IncidentService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): Incident
    {
        return DB::transaction(function () use ($data, $actor) {
            $data['incident_number'] = $this->nextIncidentNumber();
            $data['status'] ??= IncidentStatus::Reported;

            $incident = Incident::create($data);

            $this->audit->log($actor, 'created', $incident, newValues: [
                'incident_number' => $incident->incident_number,
                'type' => $incident->type->value,
                'severity' => $incident->severity->value,
            ]);

            return $incident;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Incident $incident, array $data, User $actor): Incident
    {
        return DB::transaction(function () use ($incident, $data, $actor) {
            $oldValues = $incident->only(array_keys($data));

            $incident->update($data);

            $this->audit->log($actor, 'updated', $incident, oldValues: $oldValues, newValues: $data);

            return $incident;
        });
    }

    public function delete(Incident $incident, User $actor): void
    {
        DB::transaction(function () use ($incident, $actor) {
            $this->audit->log($actor, 'deleted', $incident, oldValues: [
                'incident_number' => $incident->incident_number,
                'status' => $incident->status->value,
            ]);

            $incident->delete();
        });
    }

    public function markUnderReview(Incident $incident, User $actor): Incident
    {
        return DB::transaction(function () use ($incident, $actor) {
            if ($incident->status !== IncidentStatus::Reported) {
                throw ValidationException::withMessages([
                    'status' => 'Only a reported incident can be moved under review.',
                ]);
            }

            $previousStatus = $incident->status->value;

            $incident->status = IncidentStatus::UnderReview;
            $incident->save();

            $this->audit->log(
                $actor,
                'moved under review',
                $incident,
                oldValues: ['status' => $previousStatus],
                newValues: ['status' => $incident->status->value],
            );

            return $incident;
        });
    }

    public function resolve(Incident $incident, User $resolvedBy): Incident
    {
        return DB::transaction(function () use ($incident, $resolvedBy) {
            if ($incident->status !== IncidentStatus::UnderReview) {
                throw ValidationException::withMessages([
                    'status' => 'Only an incident under review can be resolved.',
                ]);
            }

            $previousStatus = $incident->status->value;

            $incident->status = IncidentStatus::Resolved;
            $incident->resolved_by = $resolvedBy->id;
            $incident->resolved_at = now();
            $incident->save();

            $this->audit->log(
                $resolvedBy,
                'resolved',
                $incident,
                oldValues: ['status' => $previousStatus],
                newValues: ['status' => $incident->status->value],
            );

            return $incident;
        });
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
