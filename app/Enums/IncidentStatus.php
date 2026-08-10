<?php

namespace App\Enums;

enum IncidentStatus: string
{
    case Reported = 'reported';
    case UnderReview = 'under_review';
    case Resolved = 'resolved';
}
