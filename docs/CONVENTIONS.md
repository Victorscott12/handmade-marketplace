# Quy ước code và quy trình làm việc nhóm

Mục tiêu: 5 người code song song mà **ít đụng file của nhau nhất**. Cả nhóm đọc hết file này trước khi bắt đầu.

## 1. Phân vùng thư mục (mỗi người một vùng riêng)

| TV | Vai trò | Controller | View (Blade) | Route |
|---|---|---|---|---|
| TV1 | Trưởng nhóm, Auth | `Auth/` (Breeze), `ProfileController` | `resources/views/auth/`, `layouts/`, `profile/` | `web.php`, `auth.php` |
| TV2 | Người bán | `Controllers/Seller/` | `views/seller/` | `routes/seller.php` |
| TV3 | Catalog, Wishlist | `Controllers/Catalog/`, `Buyer/WishlistController` | `views/catalog/`, `views/home.blade.php` | `routes/public.php` |
| TV4 | Giỏ hàng, Thanh toán, Đơn hàng | `Controllers/Buyer/` (Cart, Checkout, MyOrder) | `views/buyer/` | `routes/shop.php` (mục TV4) |
| TV5 | Admin, Đánh giá | `Controllers/Admin/`, `Buyer/ReviewController` | `views/admin/`, `views/reviews/` | `routes/admin.php` |

**Quy tắc vàng:**
- Chỉ sửa file trong vùng của mình. Muốn sửa file của người khác (Model, layout, migration) thì **nhắn người đó hoặc TV1**, đừng tự sửa rồi push.
- **Model và migration do TV1 giữ.** Cần thêm cột hoặc bảng thì báo TV1, hoặc tự tạo migration mới (xem mục 5) rồi báo cả nhóm.
- Không ai sửa `routes/web.php`, `layouts/app.blade.php` ngoài TV1.

## 2. Đặt tên

| Thứ | Quy ước | Ví dụ |
|---|---|---|
| Model | Số ít, PascalCase | `Product`, `OrderItem` |
| Bảng | Số nhiều, snake_case | `order_items` |
| Controller | PascalCase + `Controller` | `Seller\ProductController` |
| Phương thức resource | Dùng chuẩn Laravel | `index, create, store, show, edit, update, destroy` |
| Route name | `khu_vực.tài_nguyên.hành_động` | `seller.products.index`, `shop.cart.add`, `admin.users.index` |
| View | thư mục theo khu vực, snake_case | `seller/products/index.blade.php` |
| Form Request | `Store/Update` + tên + `Request` | `StoreProductRequest` |
| Biến, hàm | camelCase | `$totalPrice`, `calculateShippingFee()` |
| Cột DB | snake_case | `shipping_fee` |
| Nhánh Git | `loại/tên-viết-tắt/mô-tả` | `feature/tv2/product-crud` |

Trong Blade luôn dùng `route('tên')`, **không viết cứng URL**.

## 3. Quy tắc code

1. **Validate bằng Form Request** (`php artisan make:request`), không validate lẫn trong controller.
2. **Phân quyền ở Policy hoặc kiểm tra sở hữu**: người bán chỉ sửa sản phẩm của shop mình, khách chỉ xem đơn của mình.
   Ví dụ: `abort_unless($product->shop_id === auth()->user()->shop->id, 403);`
3. **Không bao giờ nối chuỗi SQL.** Dùng Eloquent hoặc query builder có binding.
4. **Blade dùng `{{ }}`** (tự escape XSS). Chỉ dùng `{!! !!}` khi chắc chắn nội dung an toàn.
5. **Mọi form POST/PUT/PATCH/DELETE phải có `@csrf`** (và `@method(...)` nếu cần).
6. **Thao tác ghi nhiều bảng dùng transaction**: `DB::transaction(function () { ... });` (đặc biệt checkout: tạo order, order_items, trừ tồn kho).
7. **Trừ tồn kho trong transaction và kiểm tra `stock >= quantity`** trước khi trừ, tránh bán quá số lượng.
8. **Eager load** để tránh N+1: `Product::with('shop', 'primaryImage')`.
9. **Giá tiền là số nguyên VND**, hiển thị bằng `number_format()` hoặc accessor `price_formatted`.
10. **Dùng hằng số enum của Model**, không gõ chuỗi tay: `Order::STATUS_PENDING`, không phải `'pending'`.
11. **Upload ảnh** lưu bằng `$request->file('image')->store('products', 'public')`, kiểm tra `mimes:jpg,jpeg,png,webp|max:2048`.
12. **Thông báo cho người dùng** bằng `->with('success', '...')` và `->with('error', '...')`; layout của TV1 sẽ hiển thị.
13. Không commit `.env`, `vendor/`, `node_modules/`, file upload trong `storage/`.

## 4. Quy trình Git

**Nhánh:**
- `main`: chỉ chứa bản chạy được. Không ai push thẳng. Chỉ TV1 merge vào.
- `develop`: nhánh tích hợp, mọi người merge vào đây qua Pull Request.
- `feature/tvX/ten-chuc-nang`: mỗi người một nhánh cho mỗi chức năng.

**Mỗi ngày làm việc:**
```bash
git checkout develop
git pull origin develop              # luôn lấy code mới nhất trước khi làm
git checkout -b feature/tv2/product-crud
# ... code ...
git add .
git commit -m "feat(seller): thêm CRUD sản phẩm"
git pull origin develop              # kéo code mới về, xử lý conflict ở máy mình
git push origin feature/tv2/product-crud
# rồi tạo Pull Request trên GitHub: feature/tv2/product-crud → develop
```

**Sau khi pull code mới của người khác, luôn chạy:**
```bash
composer install
php artisan migrate
```

**Commit message:** `loại(phạm vi): mô tả ngắn`. Các loại: `feat` (tính năng), `fix` (sửa lỗi), `refactor`, `style` (giao diện), `docs`, `chore`.

**Pull Request:** TV1 review nhanh trước khi merge, kiểm tra theo checklist:
- [ ] Chạy được: `php artisan migrate:fresh --seed` rồi thử chức năng.
- [ ] Không có file `.env`, `vendor/` trong commit.
- [ ] Chỉ sửa file trong vùng của mình (hoặc đã báo người liên quan).
- [ ] Có `@csrf`, có validate, có kiểm tra quyền sở hữu.
- [ ] Không còn `dd()`, `dump()`, `var_dump()`.

## 5. Khi cần thêm/sửa cột trong DB

1. **Không sửa file migration cũ** sau khi đã push (máy người khác đã chạy rồi, sẽ không nhận thay đổi).
2. Tạo migration mới: `php artisan make:migration add_xxx_to_yyy_table --table=yyy`
3. Push, nhắn vào nhóm: "đã thêm cột X vào bảng Y, mọi người pull rồi chạy `php artisan migrate`".
4. Cập nhật `$fillable` trong Model (báo TV1) và cập nhật lại `ERD.md`.

## 6. Giao diện chung

- TV1 đã làm `resources/views/layouts/app.blade.php` (header, menu theo vai trò, footer, vùng hiển thị thông báo).
- Mọi view khác dùng `@extends('layouts.app')`, `@section('title', '...')` và `@section('content')`. Cần thêm CSS/JS riêng cho một trang thì dùng `@push('styles')` / `@push('scripts')`.
- Cả nhóm dùng **Bootstrap 5** (đã nhúng qua CDN, không cần npm) và **Bootstrap Icons** (`<i class="bi bi-..."></i>`). Màu chủ đạo dùng class `btn-brand`, `btn-outline-brand`, `text-brand`, `bg-brand-soft` (định nghĩa trong `public/css/app.css`).
- Link tới trang của người khác mà họ **chưa làm xong** thì kiểm tra trước: `Route::has('ten.route') ? route('ten.route') : '#'` (xem cách làm trong layout).
- Component dùng lại nhiều (thẻ sản phẩm, phân trang, badge trạng thái đơn) đặt ở `resources/views/components/`. TV3 làm thẻ sản phẩm `<x-product-card>`, TV4 và TV5 dùng lại.

## 7. Họp và báo cáo tiến độ

- Họp 15 phút, 2 lần/tuần: mỗi người trả lời 3 câu **đã xong gì, sắp làm gì, đang vướng gì**.
- Ai vướng quá 1 giờ thì hỏi nhóm ngay, đừng ngồi im.
- Cuối mỗi tuần TV1 merge `develop` vào `main` nếu chạy ổn, rồi gắn tag (`v0.1`, `v0.2`...) để có bản an toàn quay lại.

## 8. Mốc bàn giao giữa các thành viên (phụ thuộc lẫn nhau)

| Ai cần gì | Từ ai | Hạn |
|---|---|---|
| Khung dự án, migration, model, đăng nhập, layout | TV1 | Hết tuần 1 (ưu tiên số 1) |
| Sản phẩm mẫu trong DB để test catalog, giỏ hàng | TV2 (hoặc seeder) | Giữa tuần 2 |
| Component `<x-product-card>` | TV3 | Đầu tuần 2 |
| Giỏ hàng + checkout tạo được `orders` | TV4 | Cuối tuần 2 |
| Đơn hàng tồn tại để người bán xử lý, khách đánh giá | TV4 → TV2, TV5 | Đầu tuần 3 |

Trong lúc chờ nhau, tự thêm dữ liệu giả bằng seeder hoặc `php artisan tinker` để không bị nghẽn.
