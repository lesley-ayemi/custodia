<?php

namespace App\Services;

use App\Enums\PrisonerStatus;
use App\Enums\ReleaseReviewStatus;
use App\Enums\ReleaseStep;
use App\Models\Prisoner;
use App\Models\ReleaseReview;
use App\Models\ReleaseReviewStep;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReleaseService
{
    public function initiate(Prisoner $prisoner, User $initiatedBy): ReleaseReview
    {
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

        return ReleaseReview::create([
            'prisoner_id' => $prisoner->id,
            'initiated_by' => $initiatedBy->id,
            'initiated_at' => now(),
            'status' => ReleaseReviewStatus::InProgress,
        ]);
    }

    public function completeStep(ReleaseReview $review, ReleaseStep $step, User $completedBy, ?string $notes): ReleaseReviewStep
    {
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

        if ($step === ReleaseStep::SupervisorApproval) {
            $this->finalizeRelease($review);
        }

        return $stepRecord;
    }

    public function cancel(ReleaseReview $review, ?string $reason): ReleaseReview
    {
        if ($review->status !== ReleaseReviewStatus::InProgress) {
            throw ValidationException::withMessages([
                'status' => 'Only an in-progress release review can be cancelled.',
            ]);
        }

        $review->status = ReleaseReviewStatus::Cancelled;
        $review->cancelled_at = now();
        $review->cancellation_reason = $reason;
        $review->save();

        return $review;
    }

    protected function finalizeRelease(ReleaseReview $review): void
    {
        $review->status = ReleaseReviewStatus::Released;
        $review->released_at = now();
        $review->save();

        $prisoner = $review->prisoner;
        $prisoner->status = PrisonerStatus::Released;
        $prisoner->save();

        $prisoner->housingAssignments()->whereNull('ended_at')->update(['ended_at' => now()]);
    }
}
