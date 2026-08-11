<?php

namespace App\Enums;

enum HearingStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Adjourned = 'adjourned';
    case Cancelled = 'cancelled';
}
