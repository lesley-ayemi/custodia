<?php

namespace App\Enums;

enum PrisonerStatus: string
{
    case InCustody = 'in_custody';
    case Released = 'released';
    case Transferred = 'transferred';
}
