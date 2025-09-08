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
        // 1. Memperbarui tabel course_reviews yang sudah ada
        // Kita pastikan kolomnya sesuai untuk ulasan kursus
        Schema::table('course_reviews', function (Blueprint $table) {
            // Menambahkan constraint unique untuk mencegah satu user mereview kursus yang sama berkali-kali
            $table->unique(['user_id', 'course_id']);
        });

        // 2. Tabel baru untuk ulasan instruktur
        Schema::create('instructor_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // Konteks kursus saat review diberikan
            $table->unsignedTinyInteger('rating'); // Rating bintang (1-5)
            $table->text('comment')->nullable(); // Ulasan tertulis
            $table->timestamps();

            $table->unique(['user_id', 'instructor_id', 'course_id']);
        });

        // 3. Tabel baru untuk menyimpan daftar pertanyaan skala Likert
        Schema::create('likert_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text'); // Teks pertanyaan, contoh: "Materi kursus mudah dipahami"
            $table->enum('category', ['course', 'instructor'])->comment('Pertanyaan ini untuk menilai kursus atau instruktur?');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Tabel baru untuk menyimpan jawaban skala Likert dari siswa
        Schema::create('likert_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // Konteks kursus saat menjawab
            $table->foreignId('likert_question_id')->constrained('likert_questions')->onDelete('cascade');
            $table->unsignedTinyInteger('answer')->comment('1: Sangat Tidak Setuju, 2: Tidak Setuju, 3: Setuju, 4: Sangat Setuju');
            $table->timestamps();

            $table->unique(['user_id', 'course_id', 'likert_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('likert_answers');
        Schema::dropIfExists('likert_questions');
        Schema::dropIfExists('instructor_reviews');

        Schema::table('course_reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'course_id']);
        });
    }
};
