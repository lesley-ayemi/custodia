<?php

namespace App\Enums;

enum EnrolmentStatus: string
{
    case Enrolled = 'enrolled';
    case Completed = 'completed';
    case Withdrawn = 'withdrawn';
}
