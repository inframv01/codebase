<?php

namespace App\Models;

use Database\Factories\AtollFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name'])]
class Atoll extends Model
{
    /** @use HasFactory<AtollFactory> */
    use HasFactory;

    public function islands(): HasMany
    {
        return $this->hasMany(Island::class);
    }
}
