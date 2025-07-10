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
        // Add columns to student_profiles
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('highest_level_of_education')->nullable()->after('website_url');
            $table->string('profession')->nullable()->after('highest_level_of_education');
            $table->string('company_or_institution_name')->nullable()->after('profession');
            $table->text('company_address')->nullable()->after('company_or_institution_name');
            $table->string('company_tax_id')->nullable()->after('company_address'); // NPWP
        });

        // Add columns to instructor_profiles
        Schema::table('instructor_profiles', function (Blueprint $table) {
            $table->string('highest_level_of_education')->nullable()->after('website_url');
            $table->string('profession')->nullable()->after('highest_level_of_education');
            $table->string('company_or_institution_name')->nullable()->after('profession');
            $table->text('company_address')->nullable()->after('company_or_institution_name');
            $table->string('company_tax_id')->nullable()->after('company_address'); // NPWP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Define rollback logic
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['highest_level_of_education', 'profession', 'company_or_institution_name', 'company_address', 'company_tax_id']);
        });

        Schema::table('instructor_profiles', function (Blueprint $table) {
            $table->dropColumn(['highest_level_of_education', 'profession', 'company_or_institution_name', 'company_address', 'company_tax_id']);
        });
    }
};
