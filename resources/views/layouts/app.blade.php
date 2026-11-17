<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') | @endif{{ config('app.name') }}</title>

    {{-- Bootstrap 5 + Bootstrap Icons qua CDN: không cần cài npm/Vite --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

{{-- ===================== HEADER ===================== --}}
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">
            <i class="bi bi-flower1 text-brand"></i> {{ config('app.name') }}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            {{-- Ô tìm kiếm: trỏ tới route của TV3 khi đã có --}}
            <form class="d-flex mx-lg-4 my-2 my-lg-0 flex-grow-1" role="search" method="GET"
                  action="{{ Route::has('products.index') ? route('products.index') : route('home') }}">
                <input class="form-control" type="search" name="q" value="{{ request('q') }}"
                       placeholder="Tìm sản phẩm thủ công..." aria-label="Tìm kiếm">
            </form>

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Đăng nhập</a></li>
                    <li class="nav-item"><a class="btn btn-brand ms-lg-2" href="{{ route('register') }}">Đăng ký</a></li>
                @else
                    @php($user = auth()->user())

                    {{-- Menu người mua (buyer và seller đều mua được) --}}
                    @if (! $user->isAdmin())
                        @if (Route::has('shop.wishlist.index'))
                            <li class="nav-item"><a class="nav-link" href="{{ route('shop.wishlist.index') }}"><i class="bi bi-heart"></i> Yêu thích</a></li>
                        @endif
                        @if (Route::has('shop.orders.index'))
                            <li class="nav-item"><a class="nav-link" href="{{ route('shop.orders.index') }}"><i class="bi bi-receipt"></i> Đơn hàng</a></li>
                        @endif
                        @if (Route::has('shop.cart.index'))
                            <li class="nav-item"><a class="nav-link" href="{{ route('shop.cart.index') }}"><i class="bi bi-bag"></i> Giỏ hàng</a></li>
                        @endif
                    @endif

                    @if ($user->isSeller())
                        <li class="nav-item"><a class="nav-link fw-semibold" href="{{ route('seller.dashboard') }}"><i class="bi bi-shop"></i> Kênh người bán</a></li>
                    @endif

                    @if ($user->isAdmin())
                        <li class="nav-item"><a class="nav-link fw-semibold" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Quản trị</a></li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ $user->avatar_url }}" alt="" class="rounded-circle" width="28" height="28">
                            <span>{{ $user->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted">{{ $user->email }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Hồ sơ của tôi</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

{{-- ===================== NỘI DUNG ===================== --}}
<main class="flex-grow-1 py-4">
    <div class="container">
        @include('partials.flash')
        @yield('content')
    </div>
</main>

{{-- ===================== FOOTER ===================== --}}
<footer class="bg-white border-top py-4 mt-auto">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 small text-muted">
        <span>&copy; {{ date('Y') }} {{ config('app.name') }}. Sàn sản phẩm thủ công và nghệ thuật.</span>
        <span>Đồ án môn PHP</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
