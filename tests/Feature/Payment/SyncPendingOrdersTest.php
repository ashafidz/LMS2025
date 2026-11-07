<?php

namespace Tests\Feature\Payment;

use App\Models\Course;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Services\XenditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SyncPendingOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::create([
            'site_name' => 'ruangstudi.id',
            'points_for_purchase' => 100,
            'vat_percentage' => 0,
            'transaction_fee_fixed' => 0,
            'transaction_fee_percentage' => 0,
        ]);
        cache()->forget('site_settings');
    }

    public function test_pending_order_20_minutes_old_settlement_becomes_paid_and_enrolled(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);

        $order = Order::factory()->create([
            'user_id' => $student->id,
            'total_amount' => 100000,
            'final_amount' => 100000,
            'status' => 'pending',
            'xendit_invoice_id' => 'inv_test',
            'created_at' => now()->subMinutes(20),
        ]);
        $order->items()->create([
            'course_id' => $course->id,
            'price_at_purchase' => $course->price,
        ]);

        $mockXendit = Mockery::mock(XenditService::class);
        $mockXendit->shouldReceive('fetchStatus')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->is($order)))
            ->andReturn([
                'status' => 'paid',
                'amount' => 100000,
            ]);
        $this->app->instance(XenditService::class, $mockXendit);

        $this->artisan('orders:sync-pending')
            ->expectsOutput('Checked: 1, paid: 1, failed: 0, unchanged: 0')
            ->assertSuccessful();

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(1, $student->fresh()->enrollments()->count());
    }

    public function test_pending_order_5_minutes_old_is_not_checked(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);

        $order = Order::factory()->create([
            'user_id' => $student->id,
            'total_amount' => 100000,
            'final_amount' => 100000,
            'status' => 'pending',
            'xendit_invoice_id' => 'inv_test',
            'created_at' => now()->subMinutes(5),
        ]);
        $order->items()->create([
            'course_id' => $course->id,
            'price_at_purchase' => $course->price,
        ]);

        $mockXendit = Mockery::mock(XenditService::class);
        $mockXendit->shouldReceive('fetchStatus')->never();
        $this->app->instance(XenditService::class, $mockXendit);

        $this->artisan('orders:sync-pending')
            ->expectsOutput('Checked: 0, paid: 0, failed: 0, unchanged: 0')
            ->assertSuccessful();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_gateway_expire_marks_order_failed(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);

        $order = Order::factory()->create([
            'user_id' => $student->id,
            'total_amount' => 100000,
            'final_amount' => 100000,
            'status' => 'pending',
            'xendit_invoice_id' => 'inv_test',
            'created_at' => now()->subMinutes(20),
        ]);
        $order->items()->create([
            'course_id' => $course->id,
            'price_at_purchase' => $course->price,
        ]);

        $mockXendit = Mockery::mock(XenditService::class);
        $mockXendit->shouldReceive('fetchStatus')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->is($order)))
            ->andReturn([
                'status' => 'failed',
                'amount' => 100000,
            ]);
        $this->app->instance(XenditService::class, $mockXendit);

        $this->artisan('orders:sync-pending')
            ->expectsOutput('Checked: 1, paid: 0, failed: 1, unchanged: 0')
            ->assertSuccessful();

        $this->assertSame('failed', $order->fresh()->status);
    }

    public function test_gateway_returns_empty_leaves_order_unchanged(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);

        $order = Order::factory()->create([
            'user_id' => $student->id,
            'total_amount' => 100000,
            'final_amount' => 100000,
            'status' => 'pending',
            'xendit_invoice_id' => 'inv_test',
            'created_at' => now()->subMinutes(20),
        ]);
        $order->items()->create([
            'course_id' => $course->id,
            'price_at_purchase' => $course->price,
        ]);

        $mockXendit = Mockery::mock(XenditService::class);
        $mockXendit->shouldReceive('fetchStatus')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->is($order)))
            ->andReturn([]);
        $this->app->instance(XenditService::class, $mockXendit);

        $this->artisan('orders:sync-pending')
            ->expectsOutput('Checked: 1, paid: 0, failed: 0, unchanged: 1')
            ->assertSuccessful();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_gateway_amount_mismatch_leaves_order_unchanged(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['price' => 100000]);

        $order = Order::factory()->create([
            'user_id' => $student->id,
            'total_amount' => 100000,
            'final_amount' => 100000,
            'status' => 'pending',
            'xendit_invoice_id' => 'inv_test',
            'created_at' => now()->subMinutes(20),
        ]);
        $order->items()->create([
            'course_id' => $course->id,
            'price_at_purchase' => $course->price,
        ]);

        $mockXendit = Mockery::mock(XenditService::class);
        $mockXendit->shouldReceive('fetchStatus')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->is($order)))
            ->andReturn([
                'status' => 'paid',
                'amount' => 50000,
            ]);
        $this->app->instance(XenditService::class, $mockXendit);

        $this->artisan('orders:sync-pending')
            ->expectsOutput('Checked: 1, paid: 0, failed: 0, unchanged: 1')
            ->assertSuccessful();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_more_than_one_chunk_of_pending_orders_are_all_processed(): void
    {
        $student = $this->makeStudent();

        Order::factory()->count(150)->create([
            'user_id' => $student->id,
            'total_amount' => 100000,
            'final_amount' => 100000,
            'status' => 'pending',
            'xendit_invoice_id' => 'inv_test',
            'created_at' => now()->subMinutes(20),
        ]);

        $mockXendit = Mockery::mock(XenditService::class);
        $mockXendit->shouldReceive('fetchStatus')
            ->times(150)
            ->andReturn([
                'status' => 'failed',
                'amount' => 100000,
            ]);
        $this->app->instance(XenditService::class, $mockXendit);

        $this->artisan('orders:sync-pending')
            ->expectsOutput('Checked: 150, paid: 0, failed: 150, unchanged: 0')
            ->assertSuccessful();

        $this->assertSame(0, Order::where('status', 'pending')->count());
    }

    public function test_order_without_xendit_invoice_id_is_skipped(): void
    {
        $student = $this->makeStudent();

        $order = Order::factory()->create([
            'user_id' => $student->id,
            'total_amount' => 100000,
            'final_amount' => 100000,
            'status' => 'pending',
            'xendit_invoice_id' => null,
            'created_at' => now()->subMinutes(20),
        ]);

        $mockXendit = Mockery::mock(XenditService::class);
        $mockXendit->shouldReceive('fetchStatus')->never();
        $this->app->instance(XenditService::class, $mockXendit);

        $this->artisan('orders:sync-pending')
            ->expectsOutput('Checked: 0, paid: 0, failed: 0, unchanged: 0')
            ->assertSuccessful();

        $this->assertSame('pending', $order->fresh()->status);
    }
}
