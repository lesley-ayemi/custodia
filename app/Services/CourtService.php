<?php

namespace App\Services;

use App\Enums\CourtCaseStatus;
use App\Enums\HearingStatus;
use App\Models\CourtCase;
use App\Models\CourtHearing;
use App\Models\Prisoner;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CourtService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createCase(Prisoner $prisoner, array $data): CourtCase
    {
        $data['prisoner_id'] = $prisoner->id;
        $data['case_number'] = $this->nextCaseNumber();
        $data['status'] ??= CourtCaseStatus::Open;

        return CourtCase::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function scheduleHearing(CourtCase $courtCase, array $data): CourtHearing
    {
        $data['court_case_id'] = $courtCase->id;
        $data['status'] ??= HearingStatus::Scheduled;

        return CourtHearing::create($data);
    }

    protected function nextCaseNumber(): string
    {
        $year = Carbon::now()->year;

        $sequence = DB::table('court_cases')
            ->where('case_number', 'like', "CASE-{$year}-%")
            ->count() + 1;

        return sprintf('CASE-%d-%04d', $year, $sequence);
    }
}
