<?php

namespace App\Models;

use Database\Factories\IslandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['atoll_id', 'name'])]
class Island extends Model
{
    /** @use HasFactory<IslandFactory> */
    use HasFactory;

    public function atoll(): BelongsTo
    {
        return $this->belongsTo(Atoll::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(IslandGroup::class, 'island_group_island');
    }
}
