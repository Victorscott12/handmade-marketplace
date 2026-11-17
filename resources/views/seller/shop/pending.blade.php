@extends('layouts.app')

@section('title', 'Cửa hàng đang chờ duyệt')

@section('content')
<div class="card shadow-sm mx-auto text-center" style="max-width: 560px">
    <div class="card-body p-5">
        @if ($shop->status === \App\Models\Shop::STATUS_PENDING)
            <i class="bi bi-hourglass-split text-brand" style="font-size: 3rem"></i>
            <h1 class="h4 fw-bold mt-3">Cửa hàng đang chờ duyệt</h1>
            <p class="text-muted">
                Yêu cầu mở <strong>{{ $shop->name }}</strong> đã được gửi.
                Bạn sẽ dùng được kênh người bán ngay khi quản trị viên duyệt.
            </p>
        @elseif ($shop->status === \App\Models\Shop::STATUS_REJECTED)
            <i class="bi bi-x-circle text-danger" style="font-size: 3rem"></i>
            <h1 class="h4 fw-bold mt-3">Cửa hàng chưa được duyệt</h1>
            <p class="text-muted mb-1">Lý do: {{ $shop->reject_reason ?: 'Không có' }}</p>
        @else
            <i class="bi bi-slash-circle text-danger" style="font-size: 3rem"></i>
            <h1 class="h4 fw-bold mt-3">Cửa hàng đang bị tạm khóa</h1>
            <p class="text-muted">Vui lòng liên hệ quản trị viên.</p>
        @endif

        <a href="{{ route('home') }}" class="btn btn-outline-brand mt-3">Về trang chủ</a>
    </div>
</div>
@endsection
