<?php

namespace App\Enums;

enum LegalStatus: string
{
    case Convicted = 'convicted';
    case OnAppeal = 'on_appeal';
    case Discharged = 'discharged';
}
