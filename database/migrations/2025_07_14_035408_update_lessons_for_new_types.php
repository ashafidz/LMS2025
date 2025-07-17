<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel baru untuk konten pelajaran tipe PowerPoint
        Schema::create('lesson_powerpoints', function (Blueprint $table) {
            $table->id();
            $table->string('file_path'); // Path ke file .pptx di storage
            $table->timestamps();
        });

        // 2. Tabel baru untuk konten pelajaran tipe Link
        // Menggunakan JSON untuk menyimpan banyak link dalam satu record
        Schema::create('lesson_link_collections', function (Blueprint $table) {
            $table->id();
            $table->json('links')->nullable(); // Format: [{"title": "Google", "url": "https://google.com"}, ...]
            $table->timestamps();
        });

        // 3. Modifikasi tabel lesson_videos yang sudah ada
        Schema::table('lesson_videos', function (Blueprint $table) {
            // Menambahkan kolom untuk membedakan sumber video
            $table->enum('source_type', ['upload', 'youtube'])->default('upload')->after('id');
            // Mengganti nama kolom agar lebih netral, dan membuatnya nullable karena link youtube tidak disimpan di sini
            $table->renameColumn('video_s3_key', 'video_path');
        });

        // Membuat kolom video_path nullable SETELAH di-rename
        Schema::table('lesson_videos', function (Blueprint $table) {
            $table->string('video_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Mengembalikan perubahan pada tabel lesson_videos
        Schema::table('lesson_videos', function (Blueprint $table) {
            $table->string('video_path')->nullable(false)->change();
            $table->renameColumn('video_path', 'video_s3_key');
            $table->dropColumn('source_type');
        });

        // Menghapus tabel baru
        Schema::dropIfExists('lesson_link_collections');
        Schema::dropIfExists('lesson_powerpoints');
    }
};
