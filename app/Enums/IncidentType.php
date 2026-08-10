<?php

namespace App\Enums;

enum IncidentType: string
{
    case PropertyDamage = 'property_damage';
    case RuleViolation = 'rule_violation';
    case Accident = 'accident';
    case Altercation = 'altercation';
    case ContrabandFound = 'contraband_found';
    case MedicalEmergency = 'medical_emergency';
}
