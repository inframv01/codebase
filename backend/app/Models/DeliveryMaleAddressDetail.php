<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['delivery_request_id', 'address', 'contact_name', 'contact_phone'])]
class DeliveryMaleAddressDetail extends Model
{
    protected function casts(): array
    {
        return [
            'address' => 'array',
        ];
    }

    public function deliveryRequest(): BelongsTo
    {
        return $this->belongsTo(DeliveryRequest::class);
    }
}
