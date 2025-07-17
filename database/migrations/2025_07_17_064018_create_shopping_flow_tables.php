<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tabel untuk menyimpan isi keranjang belanja setiap pengguna
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->timestamps();

            // Mencegah satu pengguna menambahkan kursus yang sama berkali-kali ke keranjang
            $table->unique(['user_id', 'course_id']);
        });

        // Tabel utama untuk menyimpan setiap pesanan/transaksi
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique(); // Kode unik pesanan, misal: INV/2025/07/17/XXXX
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Kolom untuk menyimpan detail harga
            $table->decimal('total_amount', 15, 2); // Harga asli sebelum diskon
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2); // Harga akhir yang harus dibayar

            // Kolom untuk kupon yang digunakan (jika ada)
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->onDelete('set null');

            // Kolom untuk status pembayaran dari Midtrans
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');

            // Kolom untuk menyimpan token pembayaran dari Midtrans
            $table->string('snap_token')->nullable();

            $table->timestamps();
        });

        // Tabel untuk menyimpan item-item di dalam setiap pesanan
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            // Menyimpan harga kursus pada saat pembelian untuk arsip
            $table->decimal('price_at_purchase', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('carts');
    }
};
