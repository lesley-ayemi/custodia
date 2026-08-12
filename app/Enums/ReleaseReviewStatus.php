<?php

namespace App\Enums;

enum ReleaseReviewStatus: string
{
    case InProgress = 'in_progress';
    case Released = 'released';
    case Cancelled = 'cancelled';
}
