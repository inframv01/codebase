<?php

namespace App\Http\Requests\Operator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stage' => ['required', Rule::in(['accepted_by_operator', 'picked_up', 'in_transit', 'arrived_at_island', 'out_for_delivery', 'delivered', 'cancelled'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
