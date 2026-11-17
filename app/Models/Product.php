<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_HIDDEN   = 'hidden';
    public const STATUS_REJECTED = 'rejected';

    // status / sold_count / avg_rating / review_count do hệ thống tự cập nhật, không mass-assign.
    protected $fillable = [
        'shop_id', 'category_id', 'name', 'slug', 'description', 'price',
        'stock', 'material', 'dimensions', 'processing_days',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'avg_rating' => 'float',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ---------- Quan hệ ----------
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ---------- Scope ----------

    /** Sản phẩm khách được phép thấy: đã duyệt VÀ shop đã được duyệt. */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->whereHas('shop', fn ($q) => $q->where('status', Shop::STATUS_APPROVED));
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        return $keyword
            ? $query->where(fn ($q) => $q->where('name', 'like', "%{$keyword}%")
                                         ->orWhere('description', 'like', "%{$keyword}%"))
            : $query;
    }

    public function scopePriceBetween(Builder $query, ?int $min, ?int $max): Builder
    {
        return $query
            ->when($min !== null, fn ($q) => $q->where('price', '>=', $min))
            ->when($max !== null, fn ($q) => $q->where('price', '<=', $max));
    }

    // ---------- Accessor ----------

    /**
     * URL ảnh chính (dùng {{ $product->image_url }} trong Blade). Chạy `php artisan storage:link` trước.
     * Nhớ eager load: Product::with('primaryImage', 'images') để tránh N+1.
     */
    public function getImageUrlAttribute(): string
    {
        $image = $this->primaryImage ?? $this->images->first();

        return $image
            ? Storage::url($image->path)
            : asset('images/no-image.svg');
    }

    public function getPriceFormattedAttribute(): string
    {
        return number_format($this->price, 0, ',', '.') . ' đ';
    }

    public function isPurchasable(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->stock > 0;
    }
}
