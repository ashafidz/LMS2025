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
        // 1. Memperbarui tabel 'coupons' yang sudah ada
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('value');
            $table->unsignedInteger('max_uses_per_user')->nullable()->after('max_uses');
        });

        // 2. Membuat tabel pivot baru untuk melacak penggunaan kupon per pengguna
        Schema::create('coupon_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
            $table->unsignedInteger('uses_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'coupon_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_user');

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'max_uses_per_user']);
        });
    }
};
