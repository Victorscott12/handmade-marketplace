<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** Tổng tiền toàn giỏ (VND). Nên eager load: $cart->load('items.product'). */
    public function getTotalAttribute(): int
    {
        return $this->items->sum(fn (CartItem $item) => $item->subtotal);
    }

    /**
     * Gom các dòng giỏ hàng theo shop_id: [shop_id => Collection<CartItem>].
     * Dùng khi checkout để tách thành nhiều đơn.
     */
    public function itemsGroupedByShop()
    {
        return $this->items->loadMissing('product')->groupBy(fn (CartItem $item) => $item->product->shop_id);
    }
}
