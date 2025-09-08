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
        // 1. Memperbarui tabel likert_questions untuk menambahkan kategori 'platform'
        Schema::table('likert_questions', function (Blueprint $table) {
            // Cara yang aman untuk mengubah enum adalah dengan mengubahnya menjadi string sementara,
            // lalu mengubahnya kembali menjadi enum dengan nilai baru.
            $table->string('category_temp')->after('category');
        });

        // Salin data lama ke kolom sementara
        DB::statement('UPDATE likert_questions SET category_temp = category');

        Schema::table('likert_questions', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->enum('category', ['course', 'instructor', 'platform'])->after('question_text');
        });

        // Salin data kembali ke kolom yang sudah diperbarui
        DB::statement('UPDATE likert_questions SET category = category_temp');

        Schema::table('likert_questions', function (Blueprint $table) {
            $table->dropColumn('category_temp');
        });


        // 2. Memperbarui tabel likert_answers agar course_id bisa kosong (nullable)
        Schema::table('likert_answers', function (Blueprint $table) {
            // Hapus foreign key constraint sementara untuk mengubah kolom
            $table->dropForeign(['course_id']);
            // Ubah kolom menjadi nullable
            $table->foreignId('course_id')->nullable()->change();
            // Tambahkan kembali foreign key constraint
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Mengembalikan perubahan pada tabel likert_answers
        Schema::table('likert_answers', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->foreignId('course_id')->nullable(false)->change();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });

        // Mengembalikan perubahan pada tabel likert_questions
        Schema::table('likert_questions', function (Blueprint $table) {
            $table->string('category_temp')->after('category');
        });
        DB::statement('UPDATE likert_questions SET category_temp = category');
        Schema::table('likert_questions', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->enum('category', ['course', 'instructor'])->after('question_text');
        });
        DB::statement('UPDATE likert_questions SET category = category_temp');
        Schema::table('likert_questions', function (Blueprint $table) {
            $table->dropColumn('category_temp');
        });
    }
};
