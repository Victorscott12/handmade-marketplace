@extends('layouts.app')

{{-- TV5 phụ trách. Bản khởi đầu do TV1 dựng; TV5 thêm menu quản lý và các trang duyệt. --}}

@section('title', 'Quản trị')

@section('content')
<h1 class="h4 fw-bold mb-4">Tổng quan hệ thống</h1>

<div class="row g-3">
    @foreach ([
        ['bi-people', 'Người dùng', $stats['users'], null],
        ['bi-shop', 'Người bán', $stats['sellers'], null],
        ['bi-hourglass-split', 'Cửa hàng chờ duyệt', $stats['pending_shops'], 'admin.shops.index'],
        ['bi-box-seam', 'Sản phẩm chờ duyệt', $stats['pending_products'], 'admin.products.index'],
        ['bi-receipt', 'Tổng đơn hàng', $stats['orders'], null],
        ['bi-cash-coin', 'Doanh thu (đơn hoàn thành)', number_format($stats['revenue'], 0, ',', '.') . ' đ', null],
    ] as [$icon, $label, $value, $routeName])
        <div class="col-6 col-lg-4">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body">
                    <i class="bi {{ $icon }}"></i>
                    <div class="text-muted small mt-2">{{ $label }}</div>
                    <div class="h4 fw-bold mb-0">{{ $value }}</div>
                    @if ($routeName && Route::has($routeName))
                        <a href="{{ route($routeName) }}" class="stretched-link small">Xem</a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
