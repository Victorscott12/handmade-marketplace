<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Đơn hàng: MỖI SHOP một đơn riêng để người bán tự quản lý trạng thái.
 * Một lần checkout có nhiều shop => nhiều orders cùng checkout_code.
 * Thông tin người nhận và tên/giá sản phẩm được lưu SNAPSHOT, không đổi khi sản phẩm sửa sau này.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();           // mã đơn hiển thị cho khách
            $table->string('checkout_code')->index();         // gom các đơn cùng 1 lần thanh toán
            $table->foreignId('user_id')->constrained()->restrictOnDelete();   // người mua
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();   // người bán

            // Snapshot địa chỉ giao hàng
            $table->string('receiver_name');
            $table->string('receiver_phone', 20);
            $table->string('shipping_address', 500);

            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedBigInteger('total');

            $table->enum('payment_method', ['cod', 'bank_transfer', 'vnpay'])->default('cod');
            $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'refunded'])->default('unpaid');
            $table->enum('status', ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'])
                  ->default('pending')->index();

            $table->text('note')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');                   // snapshot
            $table->string('product_image')->nullable();      // snapshot
            $table->unsignedBigInteger('price');              // snapshot đơn giá lúc mua
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->string('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 20);
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('transaction_code')->nullable()->index(); // mã giao dịch VNPay / ngân hàng
            $table->json('payload')->nullable();                     // dữ liệu callback gốc
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
