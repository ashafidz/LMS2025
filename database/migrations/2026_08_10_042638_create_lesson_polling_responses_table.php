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
        Schema::create('lesson_polling_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_polling_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_polling_option_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            // A user can only respond once to a specific polling lesson
            $table->unique(['lesson_polling_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_polling_responses');
    }
};
