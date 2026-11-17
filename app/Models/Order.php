<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_SHIPPING  = 'shipping';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_COD           = 'cod';
    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    public const PAYMENT_VNPAY         = 'vnpay';

    public const PAY_UNPAID   = 'unpaid';
    public const PAY_PAID     = 'paid';
    public const PAY_FAILED   = 'failed';
    public const PAY_REFUNDED = 'refunded';

    /** Luồng trạng thái hợp lệ: từ trạng thái nào được chuyển sang trạng thái nào. */
    public const TRANSITIONS = [
        self::STATUS_PENDING   => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_SHIPPING, self::STATUS_CANCELLED],
        self::STATUS_SHIPPING  => [self::STATUS_COMPLETED],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED => [],
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING   => 'Chờ xác nhận',
        self::STATUS_CONFIRMED => 'Đã xác nhận',
        self::STATUS_SHIPPING  => 'Đang giao',
        self::STATUS_COMPLETED => 'Hoàn thành',
        self::STATUS_CANCELLED => 'Đã hủy',
    ];

    protected $fillable = [
        'order_code', 'checkout_code', 'user_id', 'shop_id',
        'receiver_name', 'receiver_phone', 'shipping_address',
        'subtotal', 'shipping_fee', 'total',
        'payment_method', 'payment_status', 'status', 'note', 'cancel_reason',
    ];

    // ---------- Quan hệ ----------
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ---------- Scope ----------
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    // ---------- Helper ----------
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /** Người mua chỉ được hủy khi đơn chưa được người bán xác nhận. */
    public function canBeCancelledByBuyer(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getTotalFormattedAttribute(): string
    {
        return number_format($this->total, 0, ',', '.') . ' đ';
    }

    /** Mã đơn dạng HM-20261116-AB12CD */
    public static function generateOrderCode(): string
    {
        do {
            $code = 'HM-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (static::where('order_code', $code)->exists());

        return $code;
    }

    /** Mã gom các đơn của cùng 1 lần thanh toán. */
    public static function generateCheckoutCode(): string
    {
        return 'CO-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
    }
}
