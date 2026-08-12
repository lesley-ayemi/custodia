<?php

namespace App\Enums;

enum MedicalAlertSeverity: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
