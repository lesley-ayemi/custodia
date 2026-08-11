<?php

namespace App\Http\Requests;

use App\Models\Cell;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCellRequest extends FormRequest
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
            'code' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('cells', 'code')->ignore($this->route('cell'))],
            'capacity' => ['sometimes', 'required', 'integer', 'min:1', 'max:20'],
        ];
    }
}
