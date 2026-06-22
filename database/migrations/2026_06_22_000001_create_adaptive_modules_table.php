<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adaptive_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            // Konten diikat ke nama archetype, bukan ke kmeans_run_id,
            // agar tetap valid meski K-Means dijalankan ulang.
            $table->string('archetype_name', 100)
                  ->comment('Nama archetype kluster: Expert Innovator, Adaptive AI Explorer, dll.');

            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);

            // AI generation metadata
            $table->boolean('ai_generated')->default(false);
            $table->text('ai_prompt_used')->nullable();

            $table->timestamps();

            // Index untuk query cepat per kursus + archetype
            $table->index(['course_id', 'archetype_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adaptive_modules');
    }
};
