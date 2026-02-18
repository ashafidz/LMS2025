<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom-kolom untuk fitur revisi skor quiz oleh instruktur.
     * Skor asli (score) tetap utuh, skor revisi disimpan di kolom terpisah.
     */
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Skor revisi dari instruktur (nullable, karena belum tentu direvisi)
            $table->decimal('revised_score', 5, 2)->nullable()->after('score');

            // ID instruktur yang melakukan revisi
            $table->foreignId('revised_by')->nullable()->after('revised_score')
                ->constrained('users')->onDelete('set null');

            // Waktu revisi dilakukan
            $table->timestamp('revised_at')->nullable()->after('revised_by');

            // Catatan/alasan revisi
            $table->text('revision_note')->nullable()->after('revised_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropForeign(['revised_by']);
            $table->dropColumn(['revised_score', 'revised_by', 'revised_at', 'revision_note']);
        });
    }
};
