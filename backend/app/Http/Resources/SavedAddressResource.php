<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class SavedAddressResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'purpose' => $this->purpose,
            'address' => $this->address,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'is_default' => $this->is_default,
        ];
    }
}
