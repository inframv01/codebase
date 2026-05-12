<?php

namespace App\Models;

use Database\Factories\IslandGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name'])]
class IslandGroup extends Model
{
    /** @use HasFactory<IslandGroupFactory> */
    use HasFactory;

    public function islands(): BelongsToMany
    {
        return $this->belongsToMany(Island::class, 'island_group_island');
    }

    public function boats(): BelongsToMany
    {
        return $this->belongsToMany(Boat::class, 'boat_island_group');
    }
}
