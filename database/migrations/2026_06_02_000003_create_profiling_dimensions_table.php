<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiling_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained('profiling_components')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiling_dimensions');
    }
};
