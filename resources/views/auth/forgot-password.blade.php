@extends('layouts.app')

@section('title', 'Quên mật khẩu')

@section('content')
<div class="card shadow-sm auth-card">
    <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold mb-1">Quên mật khẩu</h1>
        <p class="text-muted mb-4">Nhập email đã đăng ký, chúng tôi sẽ gửi link đặt lại mật khẩu.</p>

        <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf
            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-brand w-100 py-2">Gửi link đặt lại mật khẩu</button>
        </form>

        @if (config('mail.default') === 'log')
            <div class="alert alert-light border small mt-4 mb-0">
                Đang ở chế độ thử nghiệm: email không gửi thật mà được ghi vào
                <code>storage/logs/laravel.log</code>. Mở file đó để lấy link.
            </div>
        @endif

        <p class="text-center mt-4 mb-0 small"><a href="{{ route('login') }}">Quay lại đăng nhập</a></p>
    </div>
</div>
@endsection
