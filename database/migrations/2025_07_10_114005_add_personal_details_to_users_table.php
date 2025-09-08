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
        Schema::table('users', function (Blueprint $table) {
            // All new columns for the 'users' table
            $table->string('phone_number')->nullable()->unique()->after('email');
            $table->text('address')->nullable()->after('profile_picture_url');
            $table->enum('gender', ['male', 'female'])->nullable()->after('address');
            $table->date('birth_date')->nullable()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'address', 'gender', 'birth_date']);
        });
    }
};
