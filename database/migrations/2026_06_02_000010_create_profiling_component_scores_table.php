<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiling_component_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('profiling_attempts')->cascadeOnDelete();
            // NULL = Component 2 (Prior Knowledge, not in profiling_components table)
            $table->foreignId('component_id')->nullable()->constrained('profiling_components')->nullOnDelete();
            // NULL = component-level score (not dimension-level)
            $table->foreignId('dimension_id')->nullable()->constrained('profiling_dimensions')->nullOnDelete();
            // For Likert: average of responses (1-5). For MCQ: percentage correct (0-100)
            $table->decimal('average_score', 5, 2);
            // For Likert: (avg_dimension / total_all_dimensions) * 100. NULL for MCQ.
            $table->decimal('contribution_pct', 5, 2)->nullable();
            // e.g. "Tinggi", "Sedang", "Rendah", "Novice", "Beginner", "Intermediate", "Expert"
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiling_component_scores');
    }
};
