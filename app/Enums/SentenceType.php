<?php

namespace App\Enums;

enum SentenceType: string
{
    case Custodial = 'custodial';
    case Suspended = 'suspended';
    case Life = 'life';
}
