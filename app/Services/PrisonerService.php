<?php

namespace App\Services;

use App\Enums\PrisonerStatus;
use App\Models\Prisoner;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PrisonerService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Prisoner
    {
        $data['prisoner_number'] = $this->nextPrisonerNumber();
        $data['status'] ??= PrisonerStatus::InCustody;

        return Prisoner::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Prisoner $prisoner, array $data): Prisoner
    {
        $prisoner->update($data);

        return $prisoner;
    }

    public function archive(Prisoner $prisoner): Prisoner
    {
        $prisoner->archived_at = now();
        $prisoner->save();

        return $prisoner;
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
