<?php

namespace App\Http\Requests\Operator;

use App\Models\TransportProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransportProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TransportProvider|null $transportProvider */
        $transportProvider = $this->route('transport_provider');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('transport_providers', 'name')->ignore($transportProvider?->id)],
            'type' => ['required', Rule::in(['boat', 'inter_island', 'inter_atoll'])],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
