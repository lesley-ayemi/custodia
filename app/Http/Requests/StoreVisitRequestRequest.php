<?php

namespace App\Http\Requests;

use App\Models\VisitRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', VisitRequest::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'visitor_id' => ['required', 'integer', 'exists:visitors,id'],
            'prisoner_id' => ['required', 'integer', 'exists:prisoners,id'],
            'relationship' => ['required', 'string', 'max:255'],
            'requested_visit_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}
