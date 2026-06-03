<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiling_likert_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('profiling_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('profiling_questions')->cascadeOnDelete();
            // Likert scale value: 1-5
            $table->tinyInteger('answer_value');
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiling_likert_answers');
    }
};
