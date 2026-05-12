<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'id_card_number' => ['sometimes', 'string', 'max:50', Rule::unique('users', 'id_card_number')->ignore($user?->id)],
            'atoll_id' => ['sometimes', 'integer', 'exists:atolls,id'],
            'island_id' => ['sometimes', 'integer', 'exists:islands,id'],
            'house_name' => ['sometimes', 'string', 'max:255'],
            'floor' => ['sometimes', 'string', 'max:50'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
        ];
    }
}
