<?php

namespace App\Enums;

enum AdmissionStatus: string
{
    case Draft = 'draft';
    case Processing = 'processing';
    case AwaitingMedical = 'awaiting_medical';
    case AwaitingHousing = 'awaiting_housing';
    case Completed = 'completed';
}
