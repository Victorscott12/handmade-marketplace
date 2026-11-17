<?php

/**
 * Khu vực dành cho NGƯỜI MUA (đã đăng nhập, role buyer).
 *  - TV3: wishlist, hồ sơ nghệ nhân đã xem...
 *  - TV4: giỏ hàng, checkout, đơn hàng của tôi
 *  - TV5: gửi đánh giá
 * Đã được web.php bọc sẵn: tên route 'shop.', middleware auth + role:buyer,seller.
 * (seller cũng có thể mua hàng nên cho phép cả 2 role.)
 * Controller đặt trong app/Http/Controllers/Buyer/
 *
 * Quy ước: mỗi TV tự nhóm route của mình dưới comment tên mình để Git ít conflict.
 */

use Illuminate\Support\Facades\Route;

// ===== TV3: Wishlist =====
// Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
// Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// ===== TV4: Giỏ hàng, Thanh toán, Đơn hàng =====
// Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
// Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
// Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
// Route::delete('/cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
// Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
// Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
// Route::get('/my-orders', [MyOrderController::class, 'index'])->name('orders.index');
// Route::get('/my-orders/{order:order_code}', [MyOrderController::class, 'show'])->name('orders.show');
// Route::patch('/my-orders/{order:order_code}/cancel', [MyOrderController::class, 'cancel'])->name('orders.cancel');

// ===== TV5: Đánh giá =====
// Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
