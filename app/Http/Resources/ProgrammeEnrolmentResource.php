<?php

namespace App\Http\Resources;

use App\Models\ProgrammeEnrolment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProgrammeEnrolment */
class ProgrammeEnrolmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attendances = $this->whenLoaded('attendances');
        $sessionCount = is_countable($attendances) ? count($attendances) : 0;
        $attendedCount = is_countable($attendances) ? collect($attendances)->where('attended', true)->count() : 0;

        return [
            'id' => $this->id,
            'programme_id' => $this->programme_id,
            'programme_name' => $this->whenLoaded('programme', fn () => $this->programme->name),
            'prisoner_id' => $this->prisoner_id,
            'prisoner_name' => $this->whenLoaded('prisoner', fn () => $this->prisoner->fullName()),
            'enrolled_by' => $this->whenLoaded('enrolledBy', fn () => $this->enrolledBy->name),
            'enrolled_at' => $this->enrolled_at->toDateString(),
            'status' => $this->status->value,
            'completed_at' => $this->completed_at?->toDateString(),
            'withdrawal_reason' => $this->withdrawal_reason,
            'session_count' => $sessionCount,
            'attended_count' => $attendedCount,
            'attendance_rate' => $sessionCount > 0 ? round($attendedCount / $sessionCount * 100) : null,
            'attendances' => ProgrammeAttendanceResource::collection($attendances),
        ];
    }
}
