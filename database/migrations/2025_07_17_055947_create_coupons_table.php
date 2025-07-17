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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('description')->nullable();

            // Tipe diskon: 'fixed' (potongan harga tetap) atau 'percent' (persentase)
            $table->enum('type', ['fixed', 'percent']);
            $table->decimal('value', 15, 2); // Nilai diskonnya

            // Lingkup kupon
            // Jika course_id NULL, kupon berlaku untuk semua kursus.
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');

            // Aturan dan Batasan
            $table->unsignedInteger('max_uses')->nullable()->comment('Batas total penggunaan kupon');
            $table->unsignedInteger('uses_count')->default(0)->comment('Sudah berapa kali digunakan');

            $table->timestamp('starts_at')->nullable()->comment('Tanggal mulai berlaku');
            $table->timestamp('expires_at')->nullable()->comment('Tanggal kedaluwarsa');

            $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists('coupons');
    }
};
