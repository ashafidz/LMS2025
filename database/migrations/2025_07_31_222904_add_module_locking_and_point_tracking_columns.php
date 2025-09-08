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
        // 1. Memperbarui tabel 'modules' untuk menambahkan kolom pengunci
        Schema::table('modules', function (Blueprint $table) {
            $table->unsignedInteger('points_required')->default(0)->after('order')->comment('Poin yang dibutuhkan untuk membuka modul ini');
        });

        // 2. Memperbarui tabel 'point_histories' untuk melacak poin per pelajaran
        Schema::table('point_histories', function (Blueprint $table) {
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('cascade')->after('course_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_histories', function (Blueprint $table) {
            $table->dropForeign(['lesson_id']);
            $table->dropColumn('lesson_id');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('points_required');
        });
    }
};
