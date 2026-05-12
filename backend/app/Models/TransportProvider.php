<?php

namespace App\Models;

use Database\Factories\TransportProviderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'contact_name', 'contact_phone', 'active'])]
class TransportProvider extends Model
{
    /** @use HasFactory<TransportProviderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function boats(): HasMany
    {
        return $this->hasMany(Boat::class);
    }
}
