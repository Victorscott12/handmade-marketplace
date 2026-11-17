@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')
<div class="card shadow-sm auth-card">
    <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold mb-1">Đăng nhập</h1>
        <p class="text-muted mb-4">Chào mừng bạn quay lại.</p>

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <a href="{{ route('password.request') }}" class="small">Quên mật khẩu?</a>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" @checked(old('remember'))>
                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
            </div>

            <button type="submit" class="btn btn-brand w-100 py-2">Đăng nhập</button>
        </form>

        <p class="text-center mt-4 mb-0 small">
            Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
        </p>

        @if (app()->isLocal())
            <div class="alert alert-light border small mt-4 mb-0">
                <strong>Tài khoản test</strong> (mật khẩu <code>password</code>):<br>
                admin@example.com · seller@example.com · seller2@example.com · buyer@example.com
            </div>
        @endif
    </div>
</div>
@endsection
