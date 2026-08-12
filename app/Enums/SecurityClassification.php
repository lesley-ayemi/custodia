<?php

namespace App\Enums;

enum SecurityClassification: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Maximum = 'maximum';
}
