<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'receiver_name', 'receiver_phone',
        'province', 'district', 'ward', 'address_line', 'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Chuỗi địa chỉ đầy đủ, dùng để lưu snapshot vào orders.shipping_address. */
    public function getFullAddressAttribute(): string
    {
        return implode(', ', [$this->address_line, $this->ward, $this->district, $this->province]);
    }
}
