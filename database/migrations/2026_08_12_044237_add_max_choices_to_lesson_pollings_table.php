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
        Schema::table('lesson_pollings', function (Blueprint $table) {
            $table->integer('max_choices')->nullable()->after('allow_multiple');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_pollings', function (Blueprint $table) {
            $table->dropColumn('max_choices');
        });
    }
};
