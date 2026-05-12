<?php

namespace App\Http\Requests\Operator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BoatScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'origin_island_id' => ['required', 'integer', 'exists:islands,id'],
            'destination_island_id' => ['required', 'integer', 'different:origin_island_id', 'exists:islands,id'],
            'departs_at' => ['required', 'date'],
            'arrives_at' => ['required', 'date', 'after:departs_at'],
            'status' => ['sometimes', Rule::in(['scheduled', 'departed', 'arrived', 'cancelled'])],
            'capacity_remaining_kg' => ['nullable', 'numeric', 'gte:0'],
        ];
    }
}
