<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    public const STATUS_VISIBLE = 'visible';
    public const STATUS_HIDDEN  = 'hidden';

    // status do Admin đổi; seller_reply/replied_at do người bán, nên không mass-assign.
    protected $fillable = ['product_id', 'user_id', 'order_item_id', 'rating', 'comment'];

    protected $casts = [
        'rating' => 'integer',
        'replied_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VISIBLE);
    }

    /**
     * Tính lại avg_rating và review_count của sản phẩm.
     * Gọi sau khi tạo, xóa hoặc ẩn review: $review->refreshProductRating();
     */
    public function refreshProductRating(): void
    {
        $stats = static::visible()
            ->where('product_id', $this->product_id)
            ->selectRaw('COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as total')
            ->first();

        Product::whereKey($this->product_id)->update([
            'avg_rating'   => round($stats->avg_rating, 2),
            'review_count' => $stats->total,
        ]);
    }
}
