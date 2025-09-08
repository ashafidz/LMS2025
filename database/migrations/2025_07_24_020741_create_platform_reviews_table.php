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
        Schema::create('platform_reviews', function (Blueprint $table) {
            $table->id();

            // Satu pengguna hanya bisa memberikan satu ulasan platform
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->unique();

            $table->unsignedTinyInteger('rating'); // Rating bintang (1-5)
            $table->text('comment')->nullable(); // Ulasan atau komentar tertulis
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('platform_reviews');
    }
};
