<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sản phẩm handmade. Giá lưu bằng số nguyên (VND) để tránh lỗi làm tròn.
 * avg_rating / review_count / sold_count là cột tổng hợp, cập nhật khi có review hoặc đơn hoàn thành.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price')->index();
            $table->unsignedInteger('stock')->default(0);
            $table->string('material')->nullable();           // chất liệu
            $table->string('dimensions')->nullable();         // kích thước
            $table->unsignedSmallInteger('processing_days')->default(1); // số ngày chuẩn bị hàng
            $table->enum('status', ['pending', 'active', 'hidden', 'rejected'])
                  ->default('pending')->index();              // Admin duyệt / Seller ẩn
            $table->string('reject_reason')->nullable();
            $table->unsignedInteger('sold_count')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->timestamps();
            $table->softDeletes();                            // xóa mềm để giữ lịch sử đơn hàng
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
