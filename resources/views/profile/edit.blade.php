@extends('layouts.app')

@section('title', 'Hồ sơ của tôi')

@section('content')
<h1 class="h4 fw-bold mb-4">Hồ sơ của tôi</h1>

<div class="row g-4">
    {{-- Thông tin cá nhân --}}
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h6 fw-bold mb-3">Thông tin cá nhân</h2>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PATCH')

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="{{ $user->avatar_url }}" alt="Ảnh đại diện" class="rounded-circle border" width="72" height="72" style="object-fit:cover">
                        <div class="flex-grow-1">
                            <label for="avatar" class="form-label small mb-1">Ảnh đại diện (JPG, PNG, WEBP, tối đa 2MB)</label>
                            <input id="avatar" type="file" name="avatar" accept="image/*"
                                   class="form-control form-control-sm @error('avatar') is-invalid @enderror">
                            @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Họ và tên</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" value="{{ $user->email }}" class="form-control" disabled>
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-brand">Lưu thay đổi</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        {{-- Đổi mật khẩu --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h6 fw-bold mb-3">Đổi mật khẩu</h2>

                <form method="POST" action="{{ route('profile.password') }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                        <input id="current_password" type="password" name="current_password" autocomplete="current-password"
                               class="form-control @error('current_password') is-invalid @enderror">
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu mới</label>
                        <input id="password" type="password" name="password" autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Nhập lại mật khẩu mới</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-outline-brand">Đổi mật khẩu</button>
                </form>
            </div>
        </div>

        {{-- Trở thành người bán --}}
        @if ($user->isBuyer())
            <div class="card shadow-sm bg-brand-soft">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold"><i class="bi bi-shop me-1"></i> Bạn làm đồ thủ công?</h2>
                    <p class="small text-muted">Mở cửa hàng để bán sản phẩm của bạn trên {{ config('app.name') }}.</p>
                    <form method="POST" action="{{ route('profile.become-seller') }}"
                          onsubmit="return confirm('Chuyển tài khoản của bạn thành tài khoản người bán?')">
                        @csrf
                        <button type="submit" class="btn btn-brand btn-sm">Đăng ký bán hàng</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
