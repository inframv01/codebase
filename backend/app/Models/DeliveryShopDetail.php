<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'delivery_request_id',
    'shop_address',
    'contact_name',
    'contact_phone',
    'quote_path',
    'items',
])]
class DeliveryShopDetail extends Model
{
    protected function casts(): array
    {
        return [
            'shop_address' => 'array',
            'items' => 'array',
        ];
    }

    public function deliveryRequest(): BelongsTo
    {
        return $this->belongsTo(DeliveryRequest::class);
    }
}
