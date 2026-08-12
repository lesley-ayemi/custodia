<?php

namespace App\Enums;

enum MovementStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Departed = 'departed';
    case Arrived = 'arrived';
    case Returned = 'returned';
    case Cancelled = 'cancelled';
}
