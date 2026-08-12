<?php

namespace App\Models;

use Database\Factories\VisitorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @use HasFactory<VisitorFactory> */
#[Fillable([
    'name',
    'date_of_birth',
    'id_type',
    'id_number',
    'phone',
    'email',
    'address',
])]
class Visitor extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'banned_at' => 'datetime',
        ];
    }

    public function visitRequests(): HasMany
    {
        return $this->hasMany(VisitRequest::class)->orderByDesc('requested_visit_date');
    }
}
