<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReleasePropertyItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('release', $this->route('propertyItem'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'released_to' => ['required', 'string', 'max:255'],
        ];
    }
}
