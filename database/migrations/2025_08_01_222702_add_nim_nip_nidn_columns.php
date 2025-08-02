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
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('nim')->nullable()->default('-')->after('profession')->comment('Nomor Induk Mahasiswa Apabila Mahasiswa');
            $table->string('nip')->nullable()->default('-')->after('nim')->comment('Nomor Induk Pegawai Apabila Pegawai');
            $table->string('nidn')->nullable()->default('-')->after('nip')->comment('Nomor Induk Dosen Apabila Dosen');
        });
        Schema::table('instructor_profiles', function (Blueprint $table) {
            $table->string('nim')->nullable()->default('-')->after('profession')->comment('Nomor Induk Mahasiswa Apabila Mahasiswa');
            $table->string('nip')->nullable()->default('-')->after('nim')->comment('Nomor Induk Pegawai Apabila Pegawai');
            $table->string('nidn')->nullable()->default('-')->after('nip')->comment('Nomor Induk Dosen Apabila Dosen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['nim', 'nip', 'nidn']);
        });
        Schema::table('instructor_profiles', function (Blueprint $table) {
            $table->dropColumn(['nim', 'nip', 'nidn']);
        });
    }
};
