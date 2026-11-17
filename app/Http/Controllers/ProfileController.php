<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /** Cập nhật thông tin cá nhân + ảnh đại diện */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'phone'  => ['nullable', 'string', 'regex:/^0\d{9,10}$/'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'phone.regex' => 'Số điện thoại phải bắt đầu bằng 0 và có 10-11 chữ số.',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Đã cập nhật hồ sơ.');
    }

    /** Đổi mật khẩu (phải nhập đúng mật khẩu hiện tại) */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => $request->password]);

        return back()->with('success', 'Đã đổi mật khẩu.');
    }

    /** Người mua muốn trở thành người bán: đổi vai trò rồi chuyển sang trang tạo cửa hàng. */
    public function becomeSeller(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isBuyer()) {
            $user->forceFill(['role' => User::ROLE_SELLER])->save();
        }

        return redirect()->route('seller.shop.create')
            ->with('success', 'Bạn đã chuyển sang tài khoản người bán. Hãy tạo cửa hàng.');
    }
}
