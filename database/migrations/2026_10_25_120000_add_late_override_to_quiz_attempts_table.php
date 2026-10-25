<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - failed_by_deadline: dicatat saat siswa mengirim kuis (gagal karena lewat batas waktu pada kuis yang tidak
     *   mengizinkan terlambat), agar perubahan batas waktu sesudahnya tidak mengubah status. Null = data lama.
     * - late_override_*: instruktur meluluskan percobaan yang terlambat (mis. internet putus); siapa, kapan, alasannya.
     *   Tanpa foreign key (cukup indeks): menambah kolom nullable tidak perlu menyalin tabel quiz_attempts di MySQL,
     *   dan tampilan menangani instruktur yang sudah dihapus.
     */
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->boolean('failed_by_deadline')->nullable();
            $table->unsignedBigInteger('late_override_by')->nullable()->index();
            $table->timestamp('late_override_at')->nullable();
            $table->string('late_override_note', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex(['late_override_by']);
            $table->dropColumn(['failed_by_deadline', 'late_override_by', 'late_override_at', 'late_override_note']);
        });
    }
};
