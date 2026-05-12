<?php

namespace App\Http\Requests\Operator;

use App\Models\Island;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IslandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Island|null $island */
        $island = $this->route('island');

        return [
            'atoll_id' => ['required', 'integer', 'exists:atolls,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('islands', 'name')
                    ->where(fn ($query) => $query->where('atoll_id', $this->input('atoll_id')))
                    ->ignore($island?->id),
            ],
        ];
    }
}
