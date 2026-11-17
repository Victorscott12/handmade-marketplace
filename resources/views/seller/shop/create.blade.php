@extends('layouts.app')

@section('title', 'Mở cửa hàng')

@section('content')
<div class="card shadow-sm mx-auto" style="max-width: 720px">
    <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold mb-1">Mở cửa hàng của bạn</h1>
        <p class="text-muted mb-4">Sau khi gửi, quản trị viên sẽ xem xét và duyệt cửa hàng.</p>

        <form method="POST" action="{{ route('seller.shop.store') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Tên cửa hàng <span class="text-danger">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Giới thiệu ngắn</label>
                <textarea id="description" name="description" rows="3"
                          class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="story" class="form-label">Câu chuyện nghệ nhân</label>
                <textarea id="story" name="story" rows="4" placeholder="Bạn bắt đầu làm đồ thủ công từ khi nào? Điều gì làm sản phẩm của bạn đặc biệt?"
                          class="form-control @error('story') is-invalid @enderror">{{ old('story') }}</textarea>
                @error('story')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label for="phone" class="form-label">Số điện thoại cửa hàng</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                           class="form-control @error('phone') is-invalid @enderror">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-7">
                    <label for="address" class="form-label">Địa chỉ xưởng</label>
                    <input id="address" type="text" name="address" value="{{ old('address') }}"
                           class="form-control @error('address') is-invalid @enderror">
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <button type="submit" class="btn btn-brand px-4">Gửi yêu cầu mở cửa hàng</button>
        </form>
    </div>
</div>
@endsection
