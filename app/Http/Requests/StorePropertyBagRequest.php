<?php

namespace App\Http\Requests;

use App\Models\PropertyItem;
use Illuminate\Foundation\Http\FormRequest;

class StorePropertyBagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PropertyItem::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'items.*.storage_location' => ['required', 'string', 'max:255'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
