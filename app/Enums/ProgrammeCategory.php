<?php

namespace App\Enums;

enum ProgrammeCategory: string
{
    case Education = 'education';
    case Counselling = 'counselling';
    case VocationalTraining = 'vocational_training';
    case SubstanceMisuse = 'substance_misuse';
    case EmploymentTraining = 'employment_training';
    case LifeSkills = 'life_skills';
    case Other = 'other';
}
