<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * TV2 phụ trách. Bản khởi đầu: vài con số cơ bản. TV2 làm tiếp biểu đồ doanh thu, sản phẩm bán chạy.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $shop = $request->user()->shop;

        $stats = [
            'products'       => $shop->products()->count(),
            'pending_orders' => $shop->orders()->where('status', Order::STATUS_PENDING)->count(),
            'completed'      => $shop->orders()->where('status', Order::STATUS_COMPLETED)->count(),
            'revenue'        => (int) $shop->orders()->where('status', Order::STATUS_COMPLETED)->sum('total'),
        ];

        return view('seller.dashboard', compact('shop', 'stats'));
    }
}
