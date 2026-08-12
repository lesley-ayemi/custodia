<?php

namespace App\Enums;

enum PrescriptionStatus: string
{
    case Active = 'active';
    case Discontinued = 'discontinued';
}
