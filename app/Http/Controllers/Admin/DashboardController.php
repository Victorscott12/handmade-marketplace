<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\View\View;

/**
 * TV5 phụ trách. Bản khởi đầu: số liệu tổng quan. TV5 làm tiếp các trang quản lý.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users'            => User::count(),
            'sellers'          => User::where('role', User::ROLE_SELLER)->count(),
            'pending_shops'    => Shop::where('status', Shop::STATUS_PENDING)->count(),
            'pending_products' => Product::where('status', Product::STATUS_PENDING)->count(),
            'orders'           => Order::count(),
            'revenue'          => (int) Order::where('status', Order::STATUS_COMPLETED)->sum('total'),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
