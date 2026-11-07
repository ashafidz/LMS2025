<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Old rows hold plain text. The default keeps them 'plain', so nothing changes on deploy.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('text_format', 10)->default('plain')->after('question_type');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('text_format');
        });
    }
};
