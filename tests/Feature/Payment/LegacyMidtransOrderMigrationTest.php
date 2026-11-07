<?php

namespace Tests\Feature\Payment;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyMidtransOrderMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration()
    {
        return require database_path('migrations/2026_11_01_100100_cancel_legacy_midtrans_pending_orders.php');
    }

    public function test_only_paid_pending_midtrans_orders_are_cancelled_and_down_restores_them(): void
    {
        $userId = $this->makeStudent()->id;
        $legacy = Order::factory()->create(['user_id' => $userId, 'snap_token' => 'snap-1', 'final_amount' => 50000]);
        $xendit = Order::factory()->create(['user_id' => $userId, 'xendit_invoice_id' => 'inv_1', 'payment_url' => 'https://x.test/inv_1']);
        $free = Order::factory()->create(['user_id' => $userId, 'snap_token' => 'snap-2', 'final_amount' => 0]);
        $paid = Order::factory()->paid()->create(['user_id' => $userId, 'snap_token' => 'snap-3']);
        $failed = Order::factory()->failed()->create(['user_id' => $userId, 'snap_token' => 'snap-4']);

        $this->migration()->up();

        $this->assertSame('cancelled', $legacy->fresh()->status);
        $this->assertSame('pending', $xendit->fresh()->status);
        $this->assertSame('pending', $free->fresh()->status);
        $this->assertSame('paid', $paid->fresh()->status);
        $this->assertSame('failed', $failed->fresh()->status);

        $this->migration()->up();
        $this->assertSame('cancelled', $legacy->fresh()->status);

        $this->migration()->down();

        $this->assertSame('pending', $legacy->fresh()->status);
        $this->assertSame('paid', $paid->fresh()->status);
        $this->assertSame('failed', $failed->fresh()->status);
    }
}
