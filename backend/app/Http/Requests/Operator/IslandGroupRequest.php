<?php

namespace App\Http\Requests\Operator;

use App\Models\IslandGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IslandGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var IslandGroup|null $islandGroup */
        $islandGroup = $this->route('island_group');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('island_groups', 'name')->ignore($islandGroup?->id)],
            'island_ids' => ['sometimes', 'array'],
            'island_ids.*' => ['integer', 'exists:islands,id'],
        ];
    }
}
