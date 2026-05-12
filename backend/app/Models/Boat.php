<?php

namespace App\Models;

use Database\Factories\BoatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['transport_provider_id', 'name', 'capacity_kg', 'active'])]
class Boat extends Model
{
    /** @use HasFactory<BoatFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'capacity_kg' => 'decimal:2',
        ];
    }

    public function transportProvider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function islandGroups(): BelongsToMany
    {
        return $this->belongsToMany(IslandGroup::class, 'boat_island_group');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(BoatSchedule::class);
    }
}
