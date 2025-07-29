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
        // 1. Membuat tabel utama untuk definisi Badge
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('icon_path'); // Path ke file gambar ikon
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Membuat tabel pivot untuk mencatat Badge yang dimiliki pengguna
        Schema::create('badge_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
            $table->timestamp('created_at')->nullable();

            // Mencegah satu pengguna mendapatkan badge yang sama berkali-kali
            $table->unique(['user_id', 'badge_id']);
        });

        // 3. Memperbarui tabel 'users' untuk menyimpan Badge yang sedang dipasang
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('equipped_badge_id')->nullable()->constrained('badges')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key sebelum menghapus kolom
            $table->dropForeign(['equipped_badge_id']);
            $table->dropColumn('equipped_badge_id');
        });

        Schema::dropIfExists('badge_user');
        Schema::dropIfExists('badges');
    }
};
