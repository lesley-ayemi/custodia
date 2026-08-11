<?php

namespace App\Enums;

enum HearingType: string
{
    case Arraignment = 'arraignment';
    case Bail = 'bail';
    case Trial = 'trial';
    case Sentencing = 'sentencing';
    case Appeal = 'appeal';
}
