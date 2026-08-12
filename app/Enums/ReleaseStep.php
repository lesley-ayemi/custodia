<?php

namespace App\Enums;

enum ReleaseStep: string
{
    case LegalVerification = 'legal_verification';
    case SentenceVerification = 'sentence_verification';
    case PropertyVerification = 'property_verification';
    case Documentation = 'documentation';
    case SupervisorApproval = 'supervisor_approval';
}
