<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PaymentResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'amount_cents' => $this->amount_cents,
            'slip_path' => $this->slip_path,
            'status' => $this->status,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
        ];
    }
}
