<?php

namespace App\Services;

use App\Enums\CourtCaseStatus;
use App\Enums\HearingStatus;
use App\Models\CourtCase;
use App\Models\CourtHearing;
use App\Models\Prisoner;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CourtService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCase(Prisoner $prisoner, array $data, User $actor): CourtCase
    {
        return DB::transaction(function () use ($prisoner, $data, $actor) {
            $data['prisoner_id'] = $prisoner->id;
            $data['case_number'] = $this->nextCaseNumber();
            $data['status'] ??= CourtCaseStatus::Open;

            $case = CourtCase::create($data);

            $this->audit->log($actor, 'opened', $case, newValues: [
                'case_number' => $case->case_number,
                'court_name' => $case->court_name,
                'charge' => $case->charge,
            ]);

            return $case;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function scheduleHearing(CourtCase $courtCase, array $data, User $actor): CourtHearing
    {
        return DB::transaction(function () use ($courtCase, $data, $actor) {
            $data['court_case_id'] = $courtCase->id;
            $data['status'] ??= HearingStatus::Scheduled;

            $hearing = CourtHearing::create($data);

            $this->audit->log($actor, 'scheduled hearing', $courtCase, newValues: [
                'type' => $hearing->type->value,
                'scheduled_at' => $hearing->scheduled_at->toIso8601String(),
            ]);

            return $hearing;
        });
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
