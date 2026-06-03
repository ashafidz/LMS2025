<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kmeans_cluster_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('kmeans_runs')->cascadeOnDelete();
            $table->foreignId('attempt_id')->constrained('profiling_attempts')->cascadeOnDelete();
            $table->tinyInteger('cluster_number');
            $table->decimal('distance_to_centroid', 10, 4)->nullable();
            $table->timestamps();

            // An attempt can only belong to one cluster per run
            $table->unique(['run_id', 'attempt_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kmeans_cluster_assignments');
    }
};
