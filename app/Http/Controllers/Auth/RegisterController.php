<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone'        => ['nullable', 'string', 'regex:/^0\d{9,10}$/'],
            'password'     => ['required', 'confirmed', Password::min(8)],
            // Chỉ cho chọn buyer hoặc seller. Không bao giờ cho tự đăng ký admin.
            'account_type' => ['required', Rule::in([User::ROLE_BUYER, User::ROLE_SELLER])],
        ], [
            'phone.regex' => 'Số điện thoại phải bắt đầu bằng 0 và có 10-11 chữ số.',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'password' => $data['password'], // tự hash nhờ cast 'hashed' trong Model
        ]);

        // role không nằm trong $fillable nên gán riêng
        $user->forceFill(['role' => $data['account_type']])->save();

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        if ($user->isSeller()) {
            return redirect()->route('seller.shop.create')
                ->with('success', 'Đăng ký thành công! Hãy tạo cửa hàng của bạn.');
        }

        return redirect()->route('home')->with('success', 'Đăng ký thành công! Chào mừng bạn.');
    }
}
