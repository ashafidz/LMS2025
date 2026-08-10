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
        Schema::create('lesson_wordcloud_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_wordcloud_id')->constrained('lesson_wordclouds')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('word');
            $table->timestamps();
            
            // Allow multiple words from same user? Usually yes, or maybe not. We will enforce unique per user per wordcloud.
            $table->unique(['lesson_wordcloud_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_wordcloud_responses');
    }
};
