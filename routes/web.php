<?php

/**
 * File route CHÍNH. Chỉ TV1 (trưởng nhóm) sửa file này.
 * Các thành viên khác chỉ thêm route vào file riêng của mình:
 *   routes/public.php (TV3), routes/shop.php (TV3, TV4, TV5), routes/seller.php (TV2), routes/admin.php (TV5)
 */

use Illuminate\Support\Facades\Route;

// ---------- Trang công khai: trang chủ, danh sách, chi tiết, cửa hàng (TV3) ----------
require __DIR__ . '/public.php';

// ---------- Khu vực người mua đã đăng nhập (TV3, TV4, TV5) ----------
// Seller cũng được mua hàng nên cho phép cả 2 vai trò.
Route::middleware(['auth', 'role:buyer,seller'])
    ->name('shop.')
    ->group(base_path('routes/shop.php'));

// ---------- Khu vực người bán (TV2) ----------
Route::middleware(['auth', 'role:seller'])
    ->prefix('seller')
    ->name('seller.')
    ->group(base_path('routes/seller.php'));

// ---------- Khu vực Admin (TV5) ----------
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

// ---------- Đăng nhập, đăng ký, quên mật khẩu, hồ sơ (TV1) ----------
require __DIR__ . '/auth.php';
