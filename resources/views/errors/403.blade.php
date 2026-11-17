@extends('layouts.app')

@section('title', 'Không có quyền truy cập')

@section('content')
<div class="text-center py-5">
    <div class="display-3 fw-bold text-brand">403</div>
    <h1 class="h4 fw-bold">Bạn không có quyền vào trang này</h1>
    <p class="text-muted">{{ $exception->getMessage() ?: 'Trang này dành cho vai trò khác.' }}</p>
    <a href="{{ route('home') }}" class="btn btn-brand mt-2">Về trang chủ</a>
</div>
@endsection
