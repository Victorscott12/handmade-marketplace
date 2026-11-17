<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Dữ liệu mẫu để cả nhóm có cùng dữ liệu test.
 * Chạy: php artisan migrate:fresh --seed
 *
 * Tất cả tài khoản có mật khẩu: password
 *   admin@example.com   (admin)
 *   seller@example.com  (seller, shop đã duyệt)
 *   seller2@example.com (seller, shop ĐANG CHỜ DUYỆT, để TV5 test chức năng duyệt shop)
 *   buyer@example.com   (buyer)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $base = ['email_verified_at' => $now, 'created_at' => $now, 'updated_at' => $now];

        // ---------- Tài khoản (dùng DB::table để không phụ thuộc $fillable) ----------
        DB::table('users')->insert($base + [
            'name' => 'Quản trị viên', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'role' => 'admin',
        ]);

        $sellerId = DB::table('users')->insertGetId($base + [
            'name' => 'Nghệ nhân Minh Anh', 'email' => 'seller@example.com', 'phone' => '0901234567',
            'password' => Hash::make('password'), 'role' => 'seller',
        ]);

        $seller2Id = DB::table('users')->insertGetId($base + [
            'name' => 'Nghệ nhân Thu Hà', 'email' => 'seller2@example.com',
            'password' => Hash::make('password'), 'role' => 'seller',
        ]);

        DB::table('users')->insert($base + [
            'name' => 'Khách Demo', 'email' => 'buyer@example.com', 'phone' => '0912345678',
            'password' => Hash::make('password'), 'role' => 'buyer',
        ]);

        // ---------- Cửa hàng ----------
        $shopId = DB::table('shops')->insertGetId([
            'user_id' => $sellerId, 'name' => 'Xưởng Gốm Minh Anh', 'slug' => 'xuong-gom-minh-anh',
            'description' => 'Gốm thủ công nặn tay, men tro tự pha.',
            'story' => 'Hơn 10 năm gắn bó với làng gốm Bát Tràng.',
            'phone' => '0901234567', 'address' => 'Bát Tràng, Gia Lâm, Hà Nội',
            'status' => 'approved', 'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('shops')->insert([
            'user_id' => $seller2Id, 'name' => 'Len Thu Hà', 'slug' => 'len-thu-ha',
            'description' => 'Đồ móc len thủ công.', 'status' => 'pending',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // ---------- Danh mục ----------
        $categoryNames = [
            'Gốm sứ', 'Đồ gỗ', 'Thêu & dệt', 'Trang sức handmade',
            'Nến & tinh dầu', 'Tranh & nghệ thuật', 'Đồ da', 'Giấy & thiệp',
        ];

        $cat = [];
        foreach ($categoryNames as $i => $name) {
            $cat[$name] = DB::table('categories')->insertGetId([
                'name' => $name, 'slug' => Str::slug($name),
                'sort_order' => $i, 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ---------- Sản phẩm mẫu (chưa có ảnh, sẽ hiện ảnh "Chưa có ảnh") ----------
        $products = [
            ['Bình gốm men lam', 'Gốm sứ', 450000, 12, 'Gốm men lam', 'Cao 25cm'],
            ['Bộ chén trà men tro', 'Gốm sứ', 320000, 8, 'Gốm men tro', '6 chén + 1 ấm'],
            ['Đĩa gốm vẽ tay hoa sen', 'Gốm sứ', 180000, 20, 'Gốm sứ', 'Đường kính 22cm'],
            ['Hộp gỗ khắc hoa văn', 'Đồ gỗ', 260000, 15, 'Gỗ hương', '15 x 10 x 6cm'],
            ['Khăn thêu tay chim hạc', 'Thêu & dệt', 150000, 30, 'Lụa tơ tằm', '50 x 50cm'],
            ['Vòng tay bạc chạm khắc', 'Trang sức handmade', 390000, 10, 'Bạc 925', 'Size tùy chỉnh'],
            ['Nến thơm sáp đậu nành', 'Nến & tinh dầu', 120000, 40, 'Sáp đậu nành, tinh dầu sả', '200g'],
            ['Thiệp giấy dó vẽ màu nước', 'Giấy & thiệp', 45000, 100, 'Giấy dó', '12 x 17cm'],
        ];

        foreach ($products as $i => [$name, $category, $price, $stock, $material, $dimensions]) {
            DB::table('products')->insert([
                'shop_id' => $shopId,
                'category_id' => $cat[$category],
                'name' => $name,
                'slug' => Str::slug($name) . '-' . ($i + 1),
                'description' => "Sản phẩm mẫu: {$name}. Làm thủ công hoàn toàn, mỗi sản phẩm có thể khác nhau đôi chút.",
                'price' => $price,
                'stock' => $stock,
                'material' => $material,
                'dimensions' => $dimensions,
                'processing_days' => 2,
                'status' => 'active',
                'created_at' => $now->copy()->subMinutes(count($products) - $i),
                'updated_at' => $now,
            ]);
        }
    }
}
