<?php

namespace App\Http\Requests\Operator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PricingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scope_type' => ['required', Rule::in(['island', 'island_group'])],
            'scope_id' => ['required', 'integer', 'min:1'],
            'service_type' => ['required', Rule::in(['post_office', 'male_address', 'shop'])],
            'fixed_cost_cents' => ['required', 'integer', 'min:0'],
            'variable_rate_cents_per_kg' => ['required', 'integer', 'min:0'],
            'min_charge_cents' => ['required', 'integer', 'min:0'],
            'requires_inspection' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
