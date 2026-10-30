<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $number
 * @property string $tracking_code
 * @property OrderStatus $status
 * @property PaymentMethod $payment_method
 * @property PaymentStatus $payment_status
 * @property string|null $payment_reference
 * @property string $customer_first_name
 * @property string $customer_last_name
 * @property string $customer_email
 * @property string $customer_phone
 * @property string|null $customer_dni
 * @property ShippingMethod $shipping_method
 * @property int|null $shipping_zone_id
 * @property string|null $shipping_address
 * @property string|null $shipping_city
 * @property string|null $shipping_province
 * @property string|null $shipping_postal_code
 * @property int $subtotal_cents
 * @property int $shipping_cost_cents
 * @property int $discount_cents
 * @property int $total_cents
 * @property string|null $notes
 * @property Carbon|null $paid_at
 * @property Carbon|null $shipped_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $canceled_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'user_id',
    'number',
    'tracking_code',
    'status',
    'payment_method',
    'payment_status',
    'payment_reference',
    'customer_first_name',
    'customer_last_name',
    'customer_email',
    'customer_phone',
    'customer_dni',
    'shipping_method',
    'shipping_zone_id',
    'shipping_address',
    'shipping_city',
    'shipping_province',
    'shipping_postal_code',
    'subtotal_cents',
    'shipping_cost_cents',
    'discount_cents',
    'total_cents',
    'notes',
    'paid_at',
    'shipped_at',
    'delivered_at',
    'canceled_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $appends = ['status_label', 'payment_method_label', 'shipping_method_label'];

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function isCanceled(): bool
    {
        return $this->status === OrderStatus::Canceled;
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->status->label());
    }

    protected function paymentMethodLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->payment_method->label());
    }

    protected function shippingMethodLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->shipping_method->label());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'shipping_method' => ShippingMethod::class,
            'subtotal_cents' => 'integer',
            'shipping_cost_cents' => 'integer',
            'discount_cents' => 'integer',
            'total_cents' => 'integer',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }
}
