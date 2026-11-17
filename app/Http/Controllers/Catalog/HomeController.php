<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

/**
 * TV3 phụ trách. Bản khởi đầu do TV1 dựng để test luồng đăng nhập, TV3 làm lại giao diện đầy đủ.
 */
class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::active()->roots()->get();

        $latestProducts = Product::public()
            ->with(['primaryImage', 'images', 'shop'])
            ->latest()
            ->take(8)
            ->get();

        return view('catalog.home', compact('categories', 'latestProducts'));
    }
}
