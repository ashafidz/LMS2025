<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data fix: pesanan berbayar yang masih pending dari masa Midtrans tidak punya invoice Xendit, jadi tidak bisa dibayar
 * lagi dan tidak pernah disinkronkan. Tandai cancelled supaya tombol "Bayar" hilang dan reservasi kupon lepas.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->where('status', 'pending')
            ->where('final_amount', '>', 0)
            ->whereNull('xendit_invoice_id')
            ->whereNotNull('snap_token')
            ->update(['status' => 'cancelled', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Status cancelled tidak di-set di tempat lain, jadi ini hanya mengembalikan baris dari up().
        DB::table('orders')
            ->where('status', 'cancelled')
            ->whereNull('xendit_invoice_id')
            ->whereNotNull('snap_token')
            ->update(['status' => 'pending', 'updated_at' => now()]);
    }
};
