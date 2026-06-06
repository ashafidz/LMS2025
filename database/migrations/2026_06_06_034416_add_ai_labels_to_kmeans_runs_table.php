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
        Schema::table('kmeans_runs', function (Blueprint $table) {
            $table->json('ai_labels')->nullable()->after('result_summary');
            $table->string('ai_labeling_status')->default('pending')->after('ai_labels');
            $table->timestamp('ai_labeling_requested_at')->nullable()->after('ai_labeling_status');
            $table->timestamp('ai_labeling_completed_at')->nullable()->after('ai_labeling_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kmeans_runs', function (Blueprint $table) {
            $table->dropColumn([
                'ai_labels',
                'ai_labeling_status',
                'ai_labeling_requested_at',
                'ai_labeling_completed_at'
            ]);
        });
    }
};
