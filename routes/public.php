<?php

/**
 * TV3 làm việc trong file này: các trang ai cũng xem được (không cần đăng nhập).
 * Controller đặt trong app/Http/Controllers/Catalog/
 *
 * Bỏ comment một route CHỈ KHI controller của nó đã tồn tại, nếu không cả nhóm sẽ bị lỗi.
 */

use App\Http\Controllers\Catalog\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Route::get('/products', [ProductController::class, 'index'])->name('products.index');          // tìm kiếm, lọc, sắp xếp
// Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');   // {product} = slug
// Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
// Route::get('/shops/{shop}', [ShopController::class, 'show'])->name('shops.show');               // trang nghệ nhân
