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
        Schema::table('lesson_polling_responses', function (Blueprint $table) {
            $table->dropForeign(['lesson_polling_id']);
            $table->dropUnique(['lesson_polling_id', 'user_id']);
            $table->unique(['lesson_polling_id', 'user_id', 'lesson_polling_option_id'], 'lpr_polling_user_option_unique');
            $table->foreign('lesson_polling_id')->references('id')->on('lesson_pollings')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_polling_responses', function (Blueprint $table) {
            $table->dropUnique('lpr_polling_user_option_unique');
            $table->unique(['lesson_polling_id', 'user_id']);
        });
    }
};
