<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // Kolom untuk menyimpan pengaturan tampilkan jawaban
            // Default 'true' berarti secara default jawaban akan ditampilkan
            $table->boolean('reveal_answers')->default(true)->after('allow_exceed_time_limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('reveal_answers');
        });
    }
};
