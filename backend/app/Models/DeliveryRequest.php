<?php

namespace App\Models;

use App\Casts\MoneyCents;
use App\Enums\DeliveryStage;
use App\Enums\DeliveryStatus;
use Database\Factories\DeliveryRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'user_id',
    'type',
    'destination_island_id',
    'transport_provider_id',
    'boat_schedule_id',
    'status',
    'current_stage',
    'fixed_cost_cents',
    'variable_cost_cents',
    'total_cost_cents',
    'requires_inspection',
    'quote_confirmed_at',
    'accepted_by_operator_id',
    'notes',
])]
class DeliveryRequest extends Model
{
    /** @use HasFactory<DeliveryRequestFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'fixed_cost_cents' => MoneyCents::class,
            'variable_cost_cents' => MoneyCents::class,
            'total_cost_cents' => MoneyCents::class,
            'requires_inspection' => 'boolean',
            'quote_confirmed_at' => 'datetime',
            'status' => DeliveryStatus::class,
            'current_stage' => DeliveryStage::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $deliveryRequest): void {
            $deliveryRequest->uuid ??= (string) Str::uuid7();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function destinationIsland(): BelongsTo
    {
        return $this->belongsTo(Island::class, 'destination_island_id');
    }

    public function transportProvider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function boatSchedule(): BelongsTo
    {
        return $this->belongsTo(BoatSchedule::class);
    }

    public function acceptedByOperator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_operator_id');
    }

    public function postOfficeDetail(): HasOne
    {
        return $this->hasOne(DeliveryPostOfficeDetail::class);
    }

    public function maleAddressDetail(): HasOne
    {
        return $this->hasOne(DeliveryMaleAddressDetail::class);
    }

    public function shopDetail(): HasOne
    {
        return $this->hasOne(DeliveryShopDetail::class);
    }

    public function stageEvents(): HasMany
    {
        return $this->hasMany(DeliveryStageEvent::class)->orderBy('occurred_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
