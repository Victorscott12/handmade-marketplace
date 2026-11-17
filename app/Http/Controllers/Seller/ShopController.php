<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * TV2 phụ trách. TV1 dựng sẵn phần tạo cửa hàng và trang chờ duyệt vì luồng đăng ký người bán cần đến.
 * TV2 thêm tiếp: edit/update thông tin shop, upload logo/banner...
 */
class ShopController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $shop = $request->user()->shop;

        if ($shop) {
            return $shop->isApproved()
                ? redirect()->route('seller.dashboard')
                : redirect()->route('seller.shop.pending');
        }

        return view('seller.shop.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->shop()->exists(), 403, 'Bạn đã có cửa hàng.');

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:shops,name'],
            'description' => ['nullable', 'string', 'max:2000'],
            'story'       => ['nullable', 'string', 'max:5000'],
            'phone'       => ['nullable', 'string', 'regex:/^0\d{9,10}$/'],
            'address'     => ['nullable', 'string', 'max:255'],
        ], [
            'phone.regex' => 'Số điện thoại phải bắt đầu bằng 0 và có 10-11 chữ số.',
        ]);

        $request->user()->shop()->create($data + [
            'slug' => $this->uniqueSlug($data['name']),
        ]);
        // status mặc định là 'pending' (xem migration), Admin sẽ duyệt.

        return redirect()->route('seller.shop.pending')
            ->with('success', 'Đã gửi yêu cầu mở cửa hàng. Vui lòng chờ Admin duyệt.');
    }

    public function pending(Request $request): View|RedirectResponse
    {
        $shop = $request->user()->shop;

        if (! $shop) {
            return redirect()->route('seller.shop.create');
        }
        if ($shop->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.shop.pending', compact('shop'));
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name) ?: 'shop';
        $original = $slug;
        $i = 2;

        while (Shop::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }
}
