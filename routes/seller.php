<?php

/**
 * TV2 (Người bán) làm việc trong file này.
 * Đã được web.php bọc sẵn: prefix '/seller', tên route 'seller.', middleware auth + role:seller.
 * Controller đặt trong app/Http/Controllers/Seller/
 *
 * Bỏ comment một route CHỈ KHI controller của nó đã tồn tại.
 */

use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\ShopController;
use Illuminate\Support\Facades\Route;

// Seller CHƯA có shop hoặc shop chưa duyệt (không qua middleware shop.approved)
Route::get('/shop/create', [ShopController::class, 'create'])->name('shop.create');
Route::post('/shop', [ShopController::class, 'store'])->name('shop.store');
Route::get('/shop/pending', [ShopController::class, 'pending'])->name('shop.pending');

// Chỉ dành cho seller có shop ĐÃ ĐƯỢC DUYỆT
Route::middleware('shop.approved')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route::get('/shop/edit', [ShopController::class, 'edit'])->name('shop.edit');
    // Route::put('/shop', [ShopController::class, 'update'])->name('shop.update');
    // Route::resource('products', ProductController::class);
    // Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    // Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    // Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
});
