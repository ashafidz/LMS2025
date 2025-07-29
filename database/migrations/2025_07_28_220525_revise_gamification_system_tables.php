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
        // 1. Memperbarui tabel 'users': Ganti 'points_balance' menjadi 'diamond_balance'
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('points_balance', 'diamond_balance');
        });

        // 2. Memperbarui tabel 'courses': Ganti 'points_price' menjadi 'diamond_price' dan ubah enum
        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('points_price', 'diamond_price');
            // Mengubah ENUM memerlukan DBAL, cara amannya adalah dengan mengubah ke string lalu kembali ke enum
            $table->string('payment_type')->default('money')->change();
        });
        DB::statement("ALTER TABLE courses CHANGE payment_type payment_type ENUM('money', 'diamonds') DEFAULT 'money'");


        // 3. Memperbarui tabel 'point_histories': Tambahkan course_id
        Schema::table('point_histories', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade')->after('user_id');
        });

        // 4. Membuat tabel baru 'course_user' untuk total poin per kursus
        Schema::create('course_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->unsignedInteger('points_earned')->default(0);
            $table->boolean('is_converted_to_diamond')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
        });

        // 5. Membuat tabel baru 'diamond_histories'
        Schema::create('diamond_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('diamonds'); // Bisa positif (dapat) atau negatif (pakai)
            $table->string('description'); // Contoh: "Konversi 700 poin dari kursus X" atau "Membeli kursus Y"
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
        Schema::dropIfExists('diamond_histories');
        Schema::dropIfExists('course_user');

        Schema::table('point_histories', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('diamond_price', 'points_price');
            $table->string('payment_type')->default('money')->change();
        });
        DB::statement("ALTER TABLE courses CHANGE payment_type payment_type ENUM('money', 'points') DEFAULT 'money'");


        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('diamond_balance', 'points_balance');
        });
    }
};
