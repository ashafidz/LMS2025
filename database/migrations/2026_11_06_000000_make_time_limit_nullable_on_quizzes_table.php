<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The quiz form says "Kosongkan jika tidak ada batas waktu" and the controller accepts an empty value,
 * but the column was created NOT NULL, so MySQL (strict mode) rejected every quiz without a time limit.
 * An empty (null) or 0 time limit already means "no time limit" everywhere it is read.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->integer('time_limit')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Quizzes saved without a limit get 0, which the app also reads as "no time limit".
        DB::table('quizzes')->whereNull('time_limit')->update(['time_limit' => 0]);

        Schema::table('quizzes', function (Blueprint $table) {
            $table->integer('time_limit')->nullable(false)->change();
        });
    }
};
