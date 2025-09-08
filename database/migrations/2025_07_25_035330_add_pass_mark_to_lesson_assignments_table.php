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
        Schema::table('lesson_assignments', function (Blueprint $table) {
            // Kolom untuk menyimpan nilai kelulusan, setelah kolom 'due_date'
            $table->unsignedTinyInteger('pass_mark')->default(75)->after('due_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lesson_assignments', function (Blueprint $table) {
            $table->dropColumn('pass_mark');
        });
    }
};
