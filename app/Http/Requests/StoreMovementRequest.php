<?php

namespace App\Http\Requests;

use App\Models\Movement;
use Illuminate\Foundation\Http\FormRequest;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Movement::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from_location' => ['required', 'string', 'max:255'],
            'to_location' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
        ];
    }
}
