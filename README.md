
# Handmade Market

**Development of a Handmade and Art Products Marketplace Platform**: đồ án môn PHP, nhóm 5 người.

Sàn thương mại điện tử cho sản phẩm thủ công và nghệ thuật, có 3 vai trò: **Người mua**, **Người bán (nghệ nhân)** và **Quản trị viên**.

Công nghệ: Laravel 12 · PHP 8.2+ · MySQL (XAMPP) · Bootstrap 5 (CDN, không cần npm)

---

## 1. Cài đặt lần đầu (mỗi thành viên làm một lần)

### Cần có sẵn
| Phần mềm | Ghi chú |
|---|---|
| **XAMPP** bản có PHP **8.2 trở lên** | Mở XAMPP Control Panel, Start **Apache** và **MySQL** |
| **Composer** | Tải tại getcomposer.org, khi cài chọn `C:\xampp\php\php.exe` |
| **Git** | Tải tại git-scm.com |

Kiểm tra trong terminal (CMD/PowerShell/Git Bash): `php -v` phải ra 8.2 trở lên, `composer -V` phải chạy được.

> **Lỗi thường gặp:** Composer báo thiếu extension `zip`, `fileinfo` hoặc `intl` → mở `C:\xampp\php\php.ini`, tìm dòng `;extension=zip` (tương tự với `fileinfo`, `intl`), **xóa dấu `;` ở đầu**, lưu lại rồi Stop/Start Apache.

### Các bước
```bash
# 1. Lấy code về (thay link bằng link repo của nhóm)
git clone <link-repo>
cd handmade-marketplace

# 2. Cài thư viện PHP (mất vài phút lần đầu)
composer install

# 3. Tạo file cấu hình riêng của máy mình
copy .env.example .env        # Windows CMD/PowerShell
# cp .env.example .env        # Git Bash / macOS

php artisan key:generate
```

**4. Tạo database:** vào http://localhost/phpmyadmin → **New** → tên `handmade_market`, collation `utf8mb4_unicode_ci` → **Create**.

(File `.env` đã để sẵn `DB_USERNAME=root`, `DB_PASSWORD=` trống, đúng với XAMPP mặc định. Máy ai đặt mật khẩu MySQL thì sửa trong `.env` của mình.)

```bash
# 5. Tạo bảng + dữ liệu mẫu
php artisan migrate:fresh --seed

# 6. Cho phép hiển thị ảnh upload
php artisan storage:link

# 7. Chạy web
php artisan serve
```

Mở **http://127.0.0.1:8000**

### Tài khoản test (mật khẩu đều là `password`)
| Email | Vai trò | Ghi chú |
|---|---|---|
| admin@example.com | Admin | |
| seller@example.com | Người bán | Shop đã duyệt, có 8 sản phẩm mẫu |
| seller2@example.com | Người bán | Shop **đang chờ duyệt** (để test chức năng duyệt shop) |
| buyer@example.com | Người mua | |

---

## 2. Làm việc hằng ngày

```bash
git checkout develop
git pull origin develop
composer install                 # phòng khi có thư viện mới
php artisan migrate              # phòng khi có bảng/cột mới
git checkout -b feature/tvX/ten-chuc-nang
```

Code xong thì commit, push nhánh của mình, rồi tạo **Pull Request vào `develop`** trên GitHub. Chi tiết xem [docs/CONVENTIONS.md](docs/CONVENTIONS.md).

**Mọi người đọc kỹ 2 file này trước khi code:**
- [docs/CONVENTIONS.md](docs/CONVENTIONS.md): ai làm ở thư mục nào, đặt tên ra sao, quy trình Git, checklist PR
- [docs/ERD.md](docs/ERD.md): sơ đồ CSDL, ý nghĩa các bảng, giá trị enum

---

## 3. Phân công

| TV | Phụ trách | File route | Thư mục controller / view |
|---|---|---|---|
| TV1 (trưởng nhóm) | CSDL, đăng nhập/đăng ký, phân quyền, layout, tích hợp Git | `web.php`, `auth.php` | `Auth/`, `ProfileController` · `auth/`, `layouts/`, `profile/` |
| TV2 | Người bán: shop, CRUD sản phẩm, đơn của shop, thống kê | `seller.php` | `Seller/` · `seller/` |
| TV3 | Trang chủ, danh sách, tìm kiếm/lọc, chi tiết, trang nghệ nhân, wishlist | `public.php`, mục TV3 trong `shop.php` | `Catalog/` · `catalog/` |
| TV4 | Giỏ hàng, checkout, thanh toán, đơn hàng của khách, email | mục TV4 trong `shop.php` | `Buyer/` · `buyer/` |
| TV5 | Admin, đánh giá, kiểm thử, báo cáo | `admin.php`, mục TV5 trong `shop.php` | `Admin/` · `admin/`, `reviews/` |

---

## 4. Đã có sẵn (TV1 làm xong)

- [x] 14 bảng CSDL (migration) + dữ liệu mẫu (seeder)
- [x] 14 Model có đủ quan hệ, hằng số trạng thái, scope (`Product::public()`, `Order::canTransitionTo()`...)
- [x] Đăng ký (chọn **mua** hoặc **bán**), đăng nhập, đăng xuất, giới hạn 5 lần đăng nhập/phút
- [x] Quên mật khẩu / đặt lại mật khẩu (email ghi vào `storage/logs/laravel.log` khi chạy thử)
- [x] Hồ sơ cá nhân, ảnh đại diện, đổi mật khẩu, nút "Đăng ký bán hàng" cho người mua
- [x] Phân quyền: middleware `role:...` và `shop.approved`, chặn tài khoản bị khóa
- [x] Đăng nhập xong tự chuyển trang theo vai trò (admin → trang quản trị, seller → kênh người bán)
- [x] Tạo cửa hàng + trang chờ duyệt cho người bán
- [x] Layout Bootstrap chung, menu đổi theo vai trò, thông báo flash, trang lỗi 403/404
- [x] Thông báo lỗi validate tiếng Việt (`lang/vi/validation.php`)
- [x] Trang khởi đầu cho trang chủ (TV3), dashboard người bán (TV2), dashboard admin (TV5): các bạn làm tiếp trên đó
- [x] Test tự động cho đăng nhập và phân quyền (`tests/Feature/AuthAndRoleTest.php`)

---

## 5. Lệnh hay dùng

| Lệnh | Tác dụng |
|---|---|
| `php artisan serve` | Chạy web |
| `php artisan migrate:fresh --seed` | Xóa sạch DB, tạo lại bảng và dữ liệu mẫu |
| `php artisan route:list` | Xem toàn bộ route (kiểm tra route của mình đã có chưa) |
| `php artisan make:controller Seller/ProductController --resource` | Tạo controller |
| `php artisan make:request StoreProductRequest` | Tạo class validate |
| `php artisan make:migration add_xxx_to_yyy_table --table=yyy` | Thêm cột mới (không sửa migration cũ!) |
| `php artisan tinker` | Thử code nhanh với DB |
| `php artisan optimize:clear` | Xóa cache khi sửa `.env`/route mà không thấy tác dụng |
| `php artisan test` | Chạy test (cần bật `extension=pdo_sqlite` trong `php.ini`) |

## 6. Gửi email thật (TV4, khi cần)

Mặc định `MAIL_MAILER=log`, email chỉ ghi vào file log. Muốn gửi thật qua Gmail, sửa `.env` của máy mình:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email-cua-ban@gmail.com
MAIL_PASSWORD=mat-khau-ung-dung-16-ky-tu
MAIL_FROM_ADDRESS=email-cua-ban@gmail.com
```
`MAIL_PASSWORD` là **App Password** tạo trong tài khoản Google (cần bật xác minh 2 bước), không phải mật khẩu Gmail. **Không commit file `.env`.**
# Handmade Market

**Development of a Handmade and Art Products Marketplace Platform**: đồ án môn PHP, nhóm 5 người.

Sàn thương mại điện tử cho sản phẩm thủ công và nghệ thuật, có 3 vai trò: **Người mua**, **Người bán (nghệ nhân)** và **Quản trị viên**.

Công nghệ: Laravel 12 · PHP 8.2+ · MySQL (XAMPP) · Bootstrap 5 (CDN, không cần npm)

---

## 1. Cài đặt lần đầu (mỗi thành viên làm một lần)

### Cần có sẵn
| Phần mềm | Ghi chú |
|---|---|
| **XAMPP** bản có PHP **8.2 trở lên** | Mở XAMPP Control Panel, Start **Apache** và **MySQL** |
| **Composer** | Tải tại getcomposer.org, khi cài chọn `C:\xampp\php\php.exe` |
| **Git** | Tải tại git-scm.com |

Kiểm tra trong terminal (CMD/PowerShell/Git Bash): `php -v` phải ra 8.2 trở lên, `composer -V` phải chạy được.

> **Lỗi thường gặp:** Composer báo thiếu extension `zip`, `fileinfo` hoặc `intl` → mở `C:\xampp\php\php.ini`, tìm dòng `;extension=zip` (tương tự với `fileinfo`, `intl`), **xóa dấu `;` ở đầu**, lưu lại rồi Stop/Start Apache.

### Các bước
```bash
# 1. Lấy code về (thay link bằng link repo của nhóm)
git clone <link-repo>
cd handmade-marketplace

# 2. Cài thư viện PHP (mất vài phút lần đầu)
composer install

# 3. Tạo file cấu hình riêng của máy mình
copy .env.example .env        # Windows CMD/PowerShell
# cp .env.example .env        # Git Bash / macOS

php artisan key:generate
```

**4. Tạo database:** vào http://localhost/phpmyadmin → **New** → tên `handmade_market`, collation `utf8mb4_unicode_ci` → **Create**.

(File `.env` đã để sẵn `DB_USERNAME=root`, `DB_PASSWORD=` trống, đúng với XAMPP mặc định. Máy ai đặt mật khẩu MySQL thì sửa trong `.env` của mình.)

```bash
# 5. Tạo bảng + dữ liệu mẫu
php artisan migrate:fresh --seed

# 6. Cho phép hiển thị ảnh upload
php artisan storage:link

# 7. Chạy web
php artisan serve
```

Mở **http://127.0.0.1:8000**

### Tài khoản test (mật khẩu đều là `password`)
| Email | Vai trò | Ghi chú |
|---|---|---|
| admin@example.com | Admin | |
| seller@example.com | Người bán | Shop đã duyệt, có 8 sản phẩm mẫu |
| seller2@example.com | Người bán | Shop **đang chờ duyệt** (để test chức năng duyệt shop) |
| buyer@example.com | Người mua | |

---

## 2. Làm việc hằng ngày

```bash
git checkout develop
git pull origin develop
composer install                 # phòng khi có thư viện mới
php artisan migrate              # phòng khi có bảng/cột mới
git checkout -b feature/tvX/ten-chuc-nang
```

Code xong thì commit, push nhánh của mình, rồi tạo **Pull Request vào `develop`** trên GitHub. Chi tiết xem [docs/CONVENTIONS.md](docs/CONVENTIONS.md).

**Mọi người đọc kỹ 2 file này trước khi code:**
- [docs/CONVENTIONS.md](docs/CONVENTIONS.md): ai làm ở thư mục nào, đặt tên ra sao, quy trình Git, checklist PR
- [docs/ERD.md](docs/ERD.md): sơ đồ CSDL, ý nghĩa các bảng, giá trị enum

---

## 3. Phân công

| TV | Phụ trách | File route | Thư mục controller / view |
|---|---|---|---|
| TV1 (trưởng nhóm) | CSDL, đăng nhập/đăng ký, phân quyền, layout, tích hợp Git | `web.php`, `auth.php` | `Auth/`, `ProfileController` · `auth/`, `layouts/`, `profile/` |
| TV2 | Người bán: shop, CRUD sản phẩm, đơn của shop, thống kê | `seller.php` | `Seller/` · `seller/` |
| TV3 | Trang chủ, danh sách, tìm kiếm/lọc, chi tiết, trang nghệ nhân, wishlist | `public.php`, mục TV3 trong `shop.php` | `Catalog/` · `catalog/` |
| TV4 | Giỏ hàng, checkout, thanh toán, đơn hàng của khách, email | mục TV4 trong `shop.php` | `Buyer/` · `buyer/` |
| TV5 | Admin, đánh giá, kiểm thử, báo cáo | `admin.php`, mục TV5 trong `shop.php` | `Admin/` · `admin/`, `reviews/` |

---

## 4. Đã có sẵn (TV1 làm xong)

- [x] 14 bảng CSDL (migration) + dữ liệu mẫu (seeder)
- [x] 14 Model có đủ quan hệ, hằng số trạng thái, scope (`Product::public()`, `Order::canTransitionTo()`...)
- [x] Đăng ký (chọn **mua** hoặc **bán**), đăng nhập, đăng xuất, giới hạn 5 lần đăng nhập/phút
- [x] Quên mật khẩu / đặt lại mật khẩu (email ghi vào `storage/logs/laravel.log` khi chạy thử)
- [x] Hồ sơ cá nhân, ảnh đại diện, đổi mật khẩu, nút "Đăng ký bán hàng" cho người mua
- [x] Phân quyền: middleware `role:...` và `shop.approved`, chặn tài khoản bị khóa
- [x] Đăng nhập xong tự chuyển trang theo vai trò (admin → trang quản trị, seller → kênh người bán)
- [x] Tạo cửa hàng + trang chờ duyệt cho người bán
- [x] Layout Bootstrap chung, menu đổi theo vai trò, thông báo flash, trang lỗi 403/404
- [x] Thông báo lỗi validate tiếng Việt (`lang/vi/validation.php`)
- [x] Trang khởi đầu cho trang chủ (TV3), dashboard người bán (TV2), dashboard admin (TV5): các bạn làm tiếp trên đó
- [x] Test tự động cho đăng nhập và phân quyền (`tests/Feature/AuthAndRoleTest.php`)

---

## 5. Lệnh hay dùng

| Lệnh | Tác dụng |
|---|---|
| `php artisan serve` | Chạy web |
| `php artisan migrate:fresh --seed` | Xóa sạch DB, tạo lại bảng và dữ liệu mẫu |
| `php artisan route:list` | Xem toàn bộ route (kiểm tra route của mình đã có chưa) |
| `php artisan make:controller Seller/ProductController --resource` | Tạo controller |
| `php artisan make:request StoreProductRequest` | Tạo class validate |
| `php artisan make:migration add_xxx_to_yyy_table --table=yyy` | Thêm cột mới (không sửa migration cũ!) |
| `php artisan tinker` | Thử code nhanh với DB |
| `php artisan optimize:clear` | Xóa cache khi sửa `.env`/route mà không thấy tác dụng |
| `php artisan test` | Chạy test (cần bật `extension=pdo_sqlite` trong `php.ini`) |

## 6. Gửi email thật (TV4, khi cần)

Mặc định `MAIL_MAILER=log`, email chỉ ghi vào file log. Muốn gửi thật qua Gmail, sửa `.env` của máy mình:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email-cua-ban@gmail.com
MAIL_PASSWORD=mat-khau-ung-dung-16-ky-tu
MAIL_FROM_ADDRESS=email-cua-ban@gmail.com
```
`MAIL_PASSWORD` là **App Password** tạo trong tài khoản Google (cần bật xác minh 2 bước), không phải mật khẩu Gmail. **Không commit file `.env`.**
