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
        // 1. Memperbarui tabel 'question_topics' untuk menambahkan kolom global
        Schema::table('question_topics', function (Blueprint $table) {
            $table->boolean('available_for_all_courses')->default(false)->after('description');
        });

        // 2. Membuat tabel pivot baru untuk hubungan banyak-ke-banyak
        Schema::create('course_question_topic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('question_topic_id')->constrained('question_topics')->onDelete('cascade');
            $table->timestamps();

            // Mencegah duplikasi link antara kursus dan topik yang sama
            $table->unique(['course_id', 'question_topic_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_question_topic');

        Schema::table('question_topics', function (Blueprint $table) {
            $table->dropColumn('available_for_all_courses');
        });
    }
};
