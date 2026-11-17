<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Giao diện dùng Bootstrap 5 nên phân trang cũng dùng mẫu Bootstrap 5
        Paginator::useBootstrapFive();

        // Chống dò mật khẩu: mỗi email + IP chỉ được thử đăng nhập 5 lần/phút.
        // Đếm riêng theo từng email, nên đổi qua lại các tài khoản test không bị khóa nhầm.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email')) . '|' . $request->ip());
        });

        // Chống spam email quên mật khẩu: 3 lần/phút cho mỗi email + IP.
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by(mb_strtolower((string) $request->input('email')) . '|' . $request->ip());
        });
    }
}
