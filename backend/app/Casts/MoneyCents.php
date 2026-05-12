<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MoneyCents implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return $value === null ? null : (int) $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return $value === null ? null : (int) $value;
    }
}
