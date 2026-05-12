<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class ContactNumbersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_numbers' => ['required', 'array', 'min:1', 'max:3'],
            'contact_numbers.*' => ['required', 'string', 'max:30'],
        ];
    }
}
