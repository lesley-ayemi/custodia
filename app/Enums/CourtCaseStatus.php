<?php

namespace App\Enums;

enum CourtCaseStatus: string
{
    case Open = 'open';
    case Adjourned = 'adjourned';
    case Closed = 'closed';
}
