<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adaptive_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adaptive_module_id')
                  ->constrained('adaptive_modules')
                  ->onDelete('cascade');

            $table->string('title');
            $table->longText('content')->nullable()
                  ->comment('Konten HTML dari TinyMCE');
            $table->integer('order')->default(0);

            // AI generation metadata
            $table->boolean('ai_generated')->default(false);
            $table->text('ai_prompt_used')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adaptive_lessons');
    }
};
