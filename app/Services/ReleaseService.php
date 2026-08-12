<?php

namespace App\Services;

use App\Enums\PrisonerStatus;
use App\Enums\ReleaseReviewStatus;
use App\Enums\ReleaseStep;
use App\Models\Prisoner;
use App\Models\ReleaseReview;
use App\Models\ReleaseReviewStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReleaseService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    public function initiate(Prisoner $prisoner, User $initiatedBy): ReleaseReview
    {
        return DB::transaction(function () use ($prisoner, $initiatedBy) {
            if ($prisoner->status !== PrisonerStatus::InCustody) {
                throw ValidationException::withMessages([
                    'prisoner' => 'Only a prisoner currently in custody can have a release scheduled.',
                ]);
            }

            $hasActiveReview = $prisoner->releaseReviews()->where('status', ReleaseReviewStatus::InProgress)->exists();

            if ($hasActiveReview) {
                throw ValidationException::withMessages([
                    'prisoner' => 'This prisoner already has a release review in progress.',
                ]);
            }

            $review = ReleaseReview::create([
                'prisoner_id' => $prisoner->id,
                'initiated_by' => $initiatedBy->id,
                'initiated_at' => now(),
                'status' => ReleaseReviewStatus::InProgress,
            ]);

            $this->audit->log($initiatedBy, 'scheduled release', $review, newValues: [
                'prisoner' => $prisoner->fullName(),
            ]);

            return $review;
        });
    }

    public function completeStep(ReleaseReview $review, ReleaseStep $step, User $completedBy, ?string $notes): ReleaseReviewStep
    {
        return DB::transaction(function () use ($review, $step, $completedBy, $notes) {
            if ($review->status !== ReleaseReviewStatus::InProgress) {
                throw ValidationException::withMessages([
                    'status' => 'This release review is not in progress.',
                ]);
            }

            $completedSteps = $review->steps()->pluck('step')->all();
            $nextRequired = collect(ReleaseStep::cases())->first(fn (ReleaseStep $case) => ! in_array($case, $completedSteps, true));

            if ($step !== $nextRequired) {
                throw ValidationException::withMessages([
                    'step' => "The next required step is {$nextRequired?->value}.",
                ]);
            }

            $stepRecord = ReleaseReviewStep::create([
                'release_review_id' => $review->id,
                'step' => $step,
                'completed_by' => $completedBy->id,
                'completed_at' => now(),
                'notes' => $notes,
            ]);

            $action = $step === ReleaseStep::SupervisorApproval ? 'approved release' : 'completed release step';
            $this->audit->log($completedBy, $action, $review, newValues: ['step' => $step->value]);

            if ($step === ReleaseStep::SupervisorApproval) {
                $this->finalizeRelease($review, $completedBy);
            }

            return $stepRecord;
        });
    }

    public function cancel(ReleaseReview $review, User $actor, ?string $reason): ReleaseReview
    {
        return DB::transaction(function () use ($review, $actor, $reason) {
            if ($review->status !== ReleaseReviewStatus::InProgress) {
                throw ValidationException::withMessages([
                    'status' => 'Only an in-progress release review can be cancelled.',
                ]);
            }

            $review->status = ReleaseReviewStatus::Cancelled;
            $review->cancelled_at = now();
            $review->cancellation_reason = $reason;
            $review->save();

            $this->audit->log($actor, 'cancelled release review', $review, newValues: ['reason' => $review->cancellation_reason]);

            return $review;
        });
    }

    protected function finalizeRelease(ReleaseReview $review, User $actor): void
    {
        $review->status = ReleaseReviewStatus::Released;
        $review->released_at = now();
        $review->save();

        $prisoner = $review->prisoner;
        $prisoner->status = PrisonerStatus::Released;
        $prisoner->save();

        $prisoner->housingAssignments()->whereNull('ended_at')->update(['ended_at' => now()]);

        $this->audit->log($actor, 'released', $prisoner, oldValues: ['status' => 'in_custody'], newValues: ['status' => 'released']);
    }
}
