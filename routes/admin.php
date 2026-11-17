<?php

/**
 * TV5 (Admin) làm việc trong file này.
 * Đã được web.php bọc sẵn: prefix '/admin', tên route 'admin.', middleware auth + role:admin.
 * Controller đặt trong app/Http/Controllers/Admin/
 *
 * Bỏ comment một route CHỈ KHI controller của nó đã tồn tại.
 */

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Route::resource('users', UserController::class)->only(['index', 'show', 'update']);
// Route::resource('categories', CategoryController::class)->except(['show']);
// Route::get('/shops', [ShopController::class, 'index'])->name('shops.index');
// Route::patch('/shops/{shop}/status', [ShopController::class, 'updateStatus'])->name('shops.status');
// Route::get('/products', [ProductController::class, 'index'])->name('products.index');
// Route::patch('/products/{product}/status', [ProductController::class, 'updateStatus'])->name('products.status');
// Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
