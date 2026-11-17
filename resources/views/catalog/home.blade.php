@extends('layouts.app')

{{-- TV3 phụ trách. Bản khởi đầu do TV1 dựng; TV3 làm lại giao diện trang chủ đầy đủ. --}}

@section('content')
{{-- Banner --}}
<section class="rounded-4 bg-brand-soft p-4 p-md-5 mb-5">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold">Đồ thủ công, làm bằng cả tấm lòng</h1>
            <p class="lead text-muted mb-4">Mua trực tiếp từ các nghệ nhân Việt: gốm, gỗ, thêu, trang sức và nhiều hơn nữa.</p>
            @guest
                <a href="{{ route('register') }}" class="btn btn-brand btn-lg">Bắt đầu ngay</a>
            @endguest
        </div>
        <div class="col-md-4 text-center d-none d-md-block">
            <i class="bi bi-flower1 text-brand" style="font-size: 7rem"></i>
        </div>
    </div>
</section>

{{-- Danh mục --}}
<section class="mb-5">
    <h2 class="h5 fw-bold mb-3">Danh mục</h2>
    <div class="d-flex flex-wrap gap-2">
        @forelse ($categories as $category)
            <a href="{{ Route::has('categories.show') ? route('categories.show', $category) : '#' }}"
               class="btn btn-outline-brand btn-sm rounded-pill">{{ $category->name }}</a>
        @empty
            <p class="text-muted">Chưa có danh mục.</p>
        @endforelse
    </div>
</section>

{{-- Sản phẩm mới --}}
<section>
    <h2 class="h5 fw-bold mb-3">Sản phẩm mới</h2>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
        @forelse ($latestProducts as $product)
            <div class="col">
                <a href="{{ Route::has('products.show') ? route('products.show', $product) : '#' }}"
                   class="card h-100 product-card text-decoration-none text-body">
                    <div class="ratio ratio-1x1">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="card-img-top">
                    </div>
                    <div class="card-body">
                        <div class="small text-muted text-truncate">{{ $product->shop->name }}</div>
                        <div class="fw-semibold text-truncate">{{ $product->name }}</div>
                        <div class="text-brand fw-bold mt-1">{{ $product->price_formatted }}</div>
                    </div>
                </a>
            </div>
        @empty
            <p class="text-muted">Chưa có sản phẩm nào.</p>
        @endforelse
    </div>
</section>
@endsection
