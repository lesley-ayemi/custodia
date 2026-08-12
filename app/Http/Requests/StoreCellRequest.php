<?php

namespace App\Http\Requests;

use App\Models\Cell;
use Illuminate\Foundation\Http\FormRequest;

class StoreCellRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', Cell::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'wing_id' => ['required', 'integer', 'exists:wings,id'],
            'code' => ['required', 'string', 'max:50', 'unique:cells,code'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }
}
