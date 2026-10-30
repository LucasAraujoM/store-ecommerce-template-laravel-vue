<?php

namespace App\Models;

use Database\Factories\ShippingZoneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property array<int, string>|null $provinces
 * @property int $cost_cents
 * @property int|null $free_from_cents
 * @property string|null $delivery_estimate
 * @property bool $is_pickup
 * @property string|null $pickup_address
 * @property bool $is_active
 * @property int $sort_order
 */
#[Fillable([
    'name',
    'provinces',
    'cost_cents',
    'free_from_cents',
    'delivery_estimate',
    'is_pickup',
    'pickup_address',
    'is_active',
    'sort_order',
])]
class ShippingZone extends Model
{
    /** @use HasFactory<ShippingZoneFactory> */
    use HasFactory;

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function costFor(int $subtotalCents): int
    {
        if ($this->free_from_cents !== null && $subtotalCents >= $this->free_from_cents) {
            return 0;
        }

        return $this->cost_cents;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provinces' => 'array',
            'cost_cents' => 'integer',
            'free_from_cents' => 'integer',
            'is_pickup' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
