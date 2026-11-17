@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="card shadow-sm auth-card">
    <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold mb-4">Đặt lại mật khẩu</h1>

        <form method="POST" action="{{ route('password.store') }}" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu mới</label>
                <input id="password" type="password" name="password" required autofocus autocomplete="new-password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Nhập lại mật khẩu mới</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="form-control">
            </div>

            <button type="submit" class="btn btn-brand w-100 py-2">Lưu mật khẩu mới</button>
        </form>
    </div>
</div>
@endsection
