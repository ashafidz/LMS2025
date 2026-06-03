<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiling_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained('profiling_components')->cascadeOnDelete();
            $table->foreignId('dimension_id')->nullable()->constrained('profiling_dimensions')->nullOnDelete();
            $table->text('question_text');
            $table->integer('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiling_questions');
    }
};
