<?php

namespace App\Http\Requests\Operator;

use App\Models\Atoll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Atoll|null $atoll */
        $atoll = $this->route('atoll');

        return [
            'code' => ['required', 'string', 'max:10', Rule::unique('atolls', 'code')->ignore($atoll?->id)],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
