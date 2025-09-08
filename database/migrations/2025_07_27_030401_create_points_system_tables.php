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
        // 1. Memperbarui tabel 'users' untuk menambahkan saldo poin
        Schema::table('users', function (Blueprint $table) {
            $table->integer('points_balance')->default(0)->after('remember_token');
        });

        // 2. Memperbarui tabel 'courses' untuk tipe pembayaran & harga poin
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('payment_type', ['money', 'points'])->default('money')->after('price');
            $table->unsignedInteger('points_price')->default(0)->after('payment_type');
        });

        // 3. Memperbarui tabel 'site_settings' untuk menyimpan nominal poin per aktivitas
        Schema::table('site_settings', function (Blueprint $table) {
            $table->unsignedInteger('points_for_purchase')->default(100)->comment('Poin saat membeli kursus');
            $table->unsignedInteger('points_for_article')->default(3)->comment('Poin saat menyelesaikan pelajaran artikel');
            $table->unsignedInteger('points_for_video')->default(5)->comment('Poin saat menyelesaikan pelajaran video');
            $table->unsignedInteger('points_for_document')->default(3)->comment('Poin saat menyelesaikan pelajaran dokumen');
            $table->unsignedInteger('points_for_quiz')->default(15)->comment('Poin saat lulus kuis');
            $table->unsignedInteger('points_for_assignment')->default(20)->comment('Poin saat lulus tugas');
        });

        // 4. Membuat tabel baru 'point_histories' untuk mencatat riwayat
        Schema::create('point_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('points'); // Bisa positif (mendapat) atau negatif (menggunakan)
            $table->string('description'); // Contoh: "Menyelesaikan pelajaran: Pengenalan Laravel"
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
        Schema::dropIfExists('point_histories');

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'points_for_purchase',
                'points_for_article',
                'points_for_video',
                'points_for_document',
                'points_for_quiz',
                'points_for_assignment'
            ]);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'points_price']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('points_balance');
        });
    }
};
