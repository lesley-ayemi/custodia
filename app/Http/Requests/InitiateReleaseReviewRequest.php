<?php

namespace App\Http\Requests;

use App\Models\ReleaseReview;
use Illuminate\Foundation\Http\FormRequest;

class InitiateReleaseReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ReleaseReview::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
