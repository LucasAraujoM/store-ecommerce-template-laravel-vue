<?php

namespace App\Models;

use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $product_id
 * @property string $size
 * @property string $color
 * @property string|null $color_hex
 * @property string|null $sku
 * @property int $stock
 * @property int|null $price_cents
 * @property bool $is_active
 */
#[Fillable(['product_id', 'size', 'color', 'color_hex', 'sku', 'stock', 'price_cents', 'is_active'])]
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    protected $appends = ['effective_price_cents', 'label'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    protected function effectivePriceCents(): Attribute
    {
        return Attribute::get(fn (): int => $this->price_cents
            ?? (int) $this->product()->value('price_cents'));
    }

    protected function label(): Attribute
    {
        return Attribute::get(fn (): string => "{$this->size} · {$this->color}");
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'price_cents' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
