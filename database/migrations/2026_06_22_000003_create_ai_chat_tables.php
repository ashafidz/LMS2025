<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('archetype_name', 100);
            $table->string('status', 50)->default('idle');
            $table->timestamps();

            $table->index(['course_id', 'archetype_name']);
        });

        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_chat_session_id')->constrained('ai_chat_sessions')->cascadeOnDelete();
            $table->string('role', 50); // user, assistant, system
            $table->longText('content');
            $table->boolean('has_action')->default(false);
            $table->json('action_data')->nullable(); // For storing parsed json to be applied
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
    }
};
