<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('archetype_name', 100)->nullable();
            $table->string('file_path');
            $table->string('original_filename');
            $table->longText('extracted_text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_references');
    }
};
