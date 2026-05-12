<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'id_card_number' => ['required', 'string', 'max:50', Rule::unique('users', 'id_card_number')],
            'atoll_id' => ['required', 'integer', 'min:1'],
            'island_id' => ['required', 'integer', 'min:1'],
            'house_name' => ['required', 'string', 'max:255'],
            'floor' => ['required', 'string', 'max:50'],
            'contact_numbers' => ['required', 'array', 'min:1', 'max:3'],
            'contact_numbers.*' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
