<?php

namespace App\Services;

use App\Enums\VisitRequestStatus;
use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Models\VisitRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VisitorService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function registerVisitor(array $data, User $actor): Visitor
    {
        return DB::transaction(function () use ($data, $actor) {
            $visitor = Visitor::create($data);

            $this->audit->log($actor, 'registered', $visitor, newValues: [
                'name' => $visitor->name,
                'id_type' => $visitor->id_type,
            ]);

            return $visitor;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function requestVisit(array $data, User $actor): VisitRequest
    {
        return DB::transaction(function () use ($data, $actor) {
            $data['requested_by'] = $actor->id;
            $data['status'] ??= VisitRequestStatus::Pending;

            $request = VisitRequest::create($data);

            $this->audit->log($actor, 'requested visit', $request, newValues: [
                'visitor' => $request->visitor->name,
                'prisoner' => $request->prisoner->fullName(),
                'requested_visit_date' => $request->requested_visit_date->toDateString(),
            ]);

            return $request;
        });
    }

    public function approveRequest(VisitRequest $request, User $actor, string $scheduledAt): VisitRequest
    {
        return DB::transaction(function () use ($request, $actor, $scheduledAt) {
            $this->guardPending($request);

            if ($request->visitor->banned_at !== null) {
                throw ValidationException::withMessages([
                    'visitor' => 'This visitor is banned and cannot be approved for a visit.',
                ]);
            }

            $request->status = VisitRequestStatus::Approved;
            $request->save();

            $this->audit->log($actor, 'approved', $request, newValues: ['status' => 'approved']);

            $visit = Visit::create([
                'visit_request_id' => $request->id,
                'prisoner_id' => $request->prisoner_id,
                'visitor_id' => $request->visitor_id,
                'scheduled_at' => $scheduledAt,
                'status' => VisitStatus::Scheduled,
            ]);

            $this->audit->log($actor, 'scheduled', $visit, newValues: [
                'scheduled_at' => $visit->scheduled_at->toIso8601String(),
            ]);

            return $request;
        });
    }

    public function rejectRequest(VisitRequest $request, User $actor, ?string $reason): VisitRequest
    {
        return DB::transaction(function () use ($request, $actor, $reason) {
            $this->guardPending($request);

            $request->status = VisitRequestStatus::Rejected;
            $request->rejection_reason = $reason;
            $request->save();

            $this->audit->log($actor, 'rejected', $request, newValues: [
                'status' => 'rejected',
                'reason' => $reason,
            ]);

            return $request;
        });
    }

    public function checkIn(Visit $visit, User $actor): Visit
    {
        return DB::transaction(function () use ($visit, $actor) {
            if ($visit->status !== VisitStatus::Scheduled) {
                throw ValidationException::withMessages([
                    'status' => 'Only a scheduled visit can be checked in.',
                ]);
            }

            $visit->status = VisitStatus::CheckedIn;
            $visit->checked_in_at = now();
            $visit->checked_in_by = $actor->id;
            $visit->save();

            $this->audit->log($actor, 'checked in visitor', $visit, newValues: ['status' => 'checked_in']);

            return $visit;
        });
    }

    public function checkOut(Visit $visit, User $actor, ?string $notes): Visit
    {
        return DB::transaction(function () use ($visit, $actor, $notes) {
            if ($visit->status !== VisitStatus::CheckedIn) {
                throw ValidationException::withMessages([
                    'status' => 'Only a checked-in visit can be checked out.',
                ]);
            }

            $visit->status = VisitStatus::Completed;
            $visit->checked_out_at = now();
            $visit->checked_out_by = $actor->id;
            $visit->notes = $notes;
            $visit->save();

            $this->audit->log($actor, 'checked out visitor', $visit, newValues: ['status' => 'completed']);

            return $visit;
        });
    }

    public function cancelVisit(Visit $visit, User $actor): Visit
    {
        return DB::transaction(function () use ($visit, $actor) {
            if ($visit->status !== VisitStatus::Scheduled) {
                throw ValidationException::withMessages([
                    'status' => 'Only a scheduled visit can be cancelled.',
                ]);
            }

            $visit->status = VisitStatus::Cancelled;
            $visit->save();

            $this->audit->log($actor, 'cancelled visit', $visit, newValues: ['status' => 'cancelled']);

            return $visit;
        });
    }

    protected function guardPending(VisitRequest $request): void
    {
        if ($request->status !== VisitRequestStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'This visit request has already been decided.',
            ]);
        }
    }
}
