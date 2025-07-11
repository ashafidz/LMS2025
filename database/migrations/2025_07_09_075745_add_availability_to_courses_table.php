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
        Schema::table('courses', function (Blueprint $table) {
            // Add columns after the 'status' column for organization
            $table->enum('availability_type', ['lifetime', 'period'])
                ->default('lifetime')
                ->after('status');

            $table->timestamp('start_date')->nullable()->after('availability_type');
            $table->timestamp('end_date')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['availability_type', 'start_date', 'end_date']);
        });
    }
};
