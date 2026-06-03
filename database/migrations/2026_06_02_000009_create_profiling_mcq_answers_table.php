<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiling_mcq_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('profiling_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('course_knowledge_questions')->cascadeOnDelete();
            $table->foreignId('selected_option_id')->constrained('course_knowledge_options')->cascadeOnDelete();
            // Cached at submission time to avoid recalculation
            $table->boolean('is_correct');
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiling_mcq_answers');
    }
};
