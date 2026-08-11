<?php

namespace App\Http\Requests;

use App\Models\Cell;
use Illuminate\Foundation\Http\FormRequest;

class StoreBlockRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:blocks,name'],
        ];
    }
}
