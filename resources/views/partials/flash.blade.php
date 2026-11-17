{{--
    Thông báo dùng chung. Trong controller:
      return back()->with('success', '...');   // xanh
      return back()->with('error', '...');     // đỏ
      return back()->with('warning', '...');   // vàng
--}}
@foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $type)
    @if (session($key))
        <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
            {{ session($key) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif
@endforeach
