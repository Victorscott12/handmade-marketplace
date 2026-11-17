@extends('layouts.app')

@section('title', 'Thao tác quá nhanh')

@section('content')
<div class="text-center py-5">
    <div class="display-3 fw-bold text-brand">429</div>
    <h1 class="h4 fw-bold">Bạn thao tác quá nhiều lần</h1>
    <p class="text-muted">Vui lòng đợi khoảng 1 phút rồi thử lại.</p>
    <a href="{{ url()->previous() }}" class="btn btn-brand mt-2">Quay lại</a>
</div>
@endsection
