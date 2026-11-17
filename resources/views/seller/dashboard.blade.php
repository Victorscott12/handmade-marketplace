@extends('layouts.app')

{{-- TV2 phụ trách. Bản khởi đầu do TV1 dựng; TV2 thêm biểu đồ, sản phẩm bán chạy, menu quản lý. --}}

@section('title', 'Kênh người bán')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">{{ $shop->name }}</h1>
        <span class="text-muted small">Kênh người bán</span>
    </div>
    @if (Route::has('seller.products.create'))
        <a href="{{ route('seller.products.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Thêm sản phẩm</a>
    @endif
</div>

<div class="row g-3">
    @foreach ([
        ['bi-box-seam', 'Sản phẩm', $stats['products']],
        ['bi-clock-history', 'Đơn chờ xác nhận', $stats['pending_orders']],
        ['bi-check2-circle', 'Đơn hoàn thành', $stats['completed']],
        ['bi-cash-coin', 'Doanh thu', number_format($stats['revenue'], 0, ',', '.') . ' đ'],
    ] as [$icon, $label, $value])
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body">
                    <i class="bi {{ $icon }}"></i>
                    <div class="text-muted small mt-2">{{ $label }}</div>
                    <div class="h4 fw-bold mb-0">{{ $value }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
