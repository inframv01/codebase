<?php

namespace App\Http\Requests\Operator;

use Illuminate\Foundation\Http\FormRequest;

class BoatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transport_provider_id' => ['required', 'integer', 'exists:transport_providers,id'],
            'name' => ['required', 'string', 'max:255'],
            'capacity_kg' => ['nullable', 'numeric', 'gt:0'],
            'active' => ['sometimes', 'boolean'],
            'island_group_ids' => ['sometimes', 'array'],
            'island_group_ids.*' => ['integer', 'exists:island_groups,id'],
        ];
    }
}
