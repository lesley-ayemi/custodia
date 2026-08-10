<?php

namespace App\Http\Requests;

use App\Models\Cell;
use Illuminate\Foundation\Http\FormRequest;

class AssignHousingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assign', Cell::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'prisoner_id' => ['required', 'integer', 'exists:prisoners,id'],
            'cell_id' => ['required', 'integer', 'exists:cells,id'],
        ];
    }
}
