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
        // Memperbarui tabel student_profiles
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('unique_id_number')->nullable()->after('headline')->comment('NIM/NIP/NIDN, dll.');
        });

        // Memperbarui tabel instructor_profiles
        Schema::table('instructor_profiles', function (Blueprint $table) {
            $table->string('unique_id_number')->nullable()->after('headline')->comment('NIM/NIP/NIDN, dll.');
        });

        // Menghapus kolom-kolom lama setelah kolom baru dibuat
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['nim', 'nip', 'nidn']);
        });
        Schema::table('instructor_profiles', function (Blueprint $table) {
            $table->dropColumn(['nim', 'nip', 'nidn']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Mengembalikan kolom-kolom lama
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('nim')->nullable();
            $table->string('nip')->nullable();
            $table->string('nidn')->nullable();
        });
        Schema::table('instructor_profiles', function (Blueprint $table) {
            $table->string('nim')->nullable();
            $table->string('nip')->nullable();
            $table->string('nidn')->nullable();
        });

        // Menghapus kolom baru
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn('unique_id_number');
        });
        Schema::table('instructor_profiles', function (Blueprint $table) {
            $table->dropColumn('unique_id_number');
        });
    }
};
