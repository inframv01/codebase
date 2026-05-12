<?php

namespace App\Models;

use Database\Factories\SavedAddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'label',
    'purpose',
    'address',
    'contact_name',
    'contact_phone',
    'is_default',
])]
class SavedAddress extends Model
{
    /** @use HasFactory<SavedAddressFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
