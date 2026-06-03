<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiling_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('theory_reference')->nullable();
            $table->enum('mechanics_type', ['likert'])->default('likert');
            $table->tinyInteger('scale_min')->default(1);
            $table->tinyInteger('scale_max')->default(5);
            $table->tinyInteger('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiling_components');
    }
};
