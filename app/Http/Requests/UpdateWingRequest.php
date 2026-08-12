<?php

namespace App\Http\Requests;

use App\Models\Cell;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWingRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('wings', 'name')
                    ->where('block_id', $this->route('wing')->block_id)
                    ->ignore($this->route('wing')),
            ],
        ];
    }
}
