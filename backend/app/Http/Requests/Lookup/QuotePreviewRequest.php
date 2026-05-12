<?php

namespace App\Http\Requests\Lookup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuotePreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['post_office', 'male_address', 'shop'])],
            'destination_island_id' => ['required', 'integer', 'exists:islands,id'],
            'weight_kg' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
