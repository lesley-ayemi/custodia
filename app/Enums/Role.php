<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Officer = 'officer';
    case Supervisor = 'supervisor';
    case Medical = 'medical';
}
