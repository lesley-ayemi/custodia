<?php

namespace App\Models;

use App\Enums\ProgrammeCategory;
use App\Enums\ProgrammeStatus;
use Database\Factories\ProgrammeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @use HasFactory<ProgrammeFactory> */
#[Fillable([
    'name',
    'category',
    'description',
    'capacity',
    'status',
])]
class Programme extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ProgrammeCategory::class,
            'status' => ProgrammeStatus::class,
        ];
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(ProgrammeEnrolment::class);
    }
}
