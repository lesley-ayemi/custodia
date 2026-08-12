<?php

namespace App\Services;

use App\Enums\PrisonerStatus;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PrisonerService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): Prisoner
    {
        return DB::transaction(function () use ($data, $actor) {
            $data['prisoner_number'] = $this->nextPrisonerNumber();
            $data['status'] ??= PrisonerStatus::InCustody;

            $prisoner = Prisoner::create($data);

            $this->audit->log($actor, 'created', $prisoner, newValues: [
                'prisoner_number' => $prisoner->prisoner_number,
                'first_name' => $prisoner->first_name,
                'last_name' => $prisoner->last_name,
            ]);

            return $prisoner;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Prisoner $prisoner, array $data, User $actor): Prisoner
    {
        return DB::transaction(function () use ($prisoner, $data, $actor) {
            $oldValues = $prisoner->only(array_keys($data));

            $prisoner->update($data);

            $this->audit->log($actor, 'updated', $prisoner, oldValues: $oldValues, newValues: $data);

            return $prisoner;
        });
    }

    public function archive(Prisoner $prisoner, User $actor): Prisoner
    {
        return DB::transaction(function () use ($prisoner, $actor) {
            $prisoner->archived_at = now();
            $prisoner->save();

            $this->audit->log($actor, 'archived', $prisoner);

            return $prisoner;
        });
    }

    protected function nextPrisonerNumber(): string
    {
        $year = Carbon::now()->year;

        $sequence = DB::table('prisoners')
            ->where('prisoner_number', 'like', "INM-{$year}-%")
            ->count() + 1;

        return sprintf('INM-%d-%04d', $year, $sequence);
    }
}
