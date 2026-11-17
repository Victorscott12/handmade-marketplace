@extends('layouts.app')

@section('title', 'Không tìm thấy trang')

@section('content')
<div class="text-center py-5">
    <div class="display-3 fw-bold text-brand">404</div>
    <h1 class="h4 fw-bold">Không tìm thấy trang</h1>
    <p class="text-muted">Trang bạn tìm có thể đã bị xóa hoặc chưa từng tồn tại.</p>
    <a href="{{ route('home') }}" class="btn btn-brand mt-2">Về trang chủ</a>
</div>
@endsection
