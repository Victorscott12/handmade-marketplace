@extends('layouts.app')

@section('title', 'Đăng ký')

@section('content')
<div class="card shadow-sm auth-card">
    <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold mb-1">Tạo tài khoản</h1>
        <p class="text-muted mb-4">Mua sắm đồ thủ công, hoặc mở cửa hàng của riêng bạn.</p>

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            {{-- Chọn loại tài khoản --}}
            <div class="row g-2 mb-4 account-type">
                <div class="col-6">
                    <input type="radio" class="btn-check" name="account_type" id="type_buyer" value="buyer"
                           @checked(old('account_type', 'buyer') === 'buyer')>
                    <label class="card h-100 p-3 text-center" for="type_buyer">
                        <i class="bi bi-bag-heart fs-3"></i>
                        <span class="fw-semibold">Tôi muốn mua</span>
                    </label>
                </div>
                <div class="col-6">
                    <input type="radio" class="btn-check" name="account_type" id="type_seller" value="seller"
                           @checked(old('account_type') === 'seller')>
                    <label class="card h-100 p-3 text-center" for="type_seller">
                        <i class="bi bi-shop fs-3"></i>
                        <span class="fw-semibold">Tôi muốn bán</span>
                    </label>
                </div>
                @error('account_type')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Họ và tên</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại <span class="text-muted small">(không bắt buộc)</span></label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                       class="form-control @error('phone') is-invalid @enderror">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="form-control @error('password') is-invalid @enderror">
                <div class="form-text">Ít nhất 8 ký tự.</div>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Nhập lại mật khẩu</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="form-control">
            </div>

            <button type="submit" class="btn btn-brand w-100 py-2">Đăng ký</button>
        </form>

        <p class="text-center mt-4 mb-0 small">
            Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
        </p>
    </div>
</div>
@endsection
