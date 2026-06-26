<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            $table->string('lessonpoin_title')->nullable()->after('video_url');
            $table->text('lessonpoin_description')->nullable()->after('lessonpoin_title');
        });
    }

    public function down(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            $table->dropColumn(['lessonpoin_title', 'lessonpoin_description']);
        });
    }
};
