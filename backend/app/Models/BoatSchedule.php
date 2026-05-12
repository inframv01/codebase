<?php

namespace App\Models;

use Database\Factories\BoatScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'boat_id',
    'origin_island_id',
    'destination_island_id',
    'departs_at',
    'arrives_at',
    'status',
    'capacity_remaining_kg',
])]
class BoatSchedule extends Model
{
    /** @use HasFactory<BoatScheduleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'departs_at' => 'datetime',
            'arrives_at' => 'datetime',
            'capacity_remaining_kg' => 'decimal:2',
        ];
    }

    public function boat(): BelongsTo
    {
        return $this->belongsTo(Boat::class);
    }

    public function originIsland(): BelongsTo
    {
        return $this->belongsTo(Island::class, 'origin_island_id');
    }

    public function destinationIsland(): BelongsTo
    {
        return $this->belongsTo(Island::class, 'destination_island_id');
    }
}
