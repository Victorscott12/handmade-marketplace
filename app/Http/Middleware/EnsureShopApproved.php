<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Người bán phải có shop đã được Admin duyệt mới vào được khu vực quản lý bán hàng. */
class EnsureShopApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $shop = $request->user()?->shop;

        if (! $shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Bạn cần tạo cửa hàng trước.');
        }

        if (! $shop->isApproved()) {
            return redirect()->route('seller.shop.pending')
                ->with('warning', 'Cửa hàng của bạn đang chờ Admin duyệt.');
        }

        return $next($request);
    }
}
