<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_BUYER  = 'buyer';
    public const ROLE_SELLER = 'seller';
    public const ROLE_ADMIN  = 'admin';

    public const STATUS_ACTIVE  = 'active';
    public const STATUS_BLOCKED = 'blocked';

    // KHÔNG đưa 'role' và 'status' vào fillable để tránh người dùng tự nâng quyền qua form.
    protected $fillable = ['name', 'email', 'password', 'phone', 'avatar'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ---------- Quan hệ ----------
    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /** Đơn hàng với tư cách NGƯỜI MUA */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlistProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    // ---------- Helper ----------
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSeller(): bool
    {
        return $this->role === self::ROLE_SELLER;
    }

    public function isBuyer(): bool
    {
        return $this->role === self::ROLE_BUYER;
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    /** Tên route của trang chính sau khi đăng nhập, theo vai trò. */
    public function homeRoute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN  => 'admin.dashboard',
            self::ROLE_SELLER => 'seller.dashboard',
            default           => 'home',
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return \Illuminate\Support\Facades\Storage::url($this->avatar);
        }

        // Chưa có ảnh: tạo ảnh tròn chứa chữ cái đầu của tên (không cần mạng)
        $initial = e(mb_strtoupper(mb_substr(trim($this->name) ?: '?', 0, 1)));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64">'
             . '<circle cx="32" cy="32" r="32" fill="#b5651d"/>'
             . '<text x="32" y="42" font-family="Arial,sans-serif" font-size="28" fill="#fff" text-anchor="middle">'
             . $initial . '</text></svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /** Lấy giỏ hàng, tự tạo nếu chưa có. */
    public function getOrCreateCart(): Cart
    {
        return $this->cart ?? $this->cart()->create();
    }
}
