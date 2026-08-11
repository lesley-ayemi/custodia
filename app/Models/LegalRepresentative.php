<?php

namespace App\Models;

use Database\Factories\LegalRepresentativeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @use HasFactory<LegalRepresentativeFactory> */
#[Fillable(['name', 'firm_name', 'phone', 'email'])]
class LegalRepresentative extends Model
{
    use HasFactory;

    public function courtCases(): HasMany
    {
        return $this->hasMany(CourtCase::class);
    }
}
