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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul kuis
            $table->text('description')->nullable(); // Deskripsi atau instruksi kuis
            $table->integer('pass_mark'); // Nilai minimum kelulusan (dalam persen)
            $table->integer('time_limit'); // Batas waktu pengerjaan (dalam menit)
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
