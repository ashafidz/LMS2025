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
        // 1. Membuat tabel baru untuk pengaturan situs
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            // DITAMBAHKAN: Kolom untuk Nama Website
            $table->string('site_name')->default('Nama Website Anda');

            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('npwp')->nullable();
            $table->string('logo_path')->nullable();

            // Kolom untuk PPN
            $table->decimal('vat_percentage', 5, 2)->default(11.00);

            // Kolom untuk Biaya Transaksi
            $table->decimal('transaction_fee_fixed', 15, 2)->default(0);
            $table->decimal('transaction_fee_percentage', 5, 2)->default(0);

            $table->timestamps();
        });

        // 2. Memperbarui tabel orders yang sudah ada
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('vat_amount', 15, 2)->default(0)->after('discount_amount');
            $table->decimal('transaction_fee_amount', 15, 2)->default(0)->after('vat_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['vat_amount', 'transaction_fee_amount']);
        });
        Schema::dropIfExists('site_settings');
    }
};
