<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderSettlementService
{
    /**
     * Mark an order as paid, enroll student, award points, record coupon usage, and clear cart.
     */
    public function markPaid(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $lockedOrder) {
                return false;
            }

            // Idempotent: if already paid, do nothing
            if ($lockedOrder->status === 'paid') {
                return false;
            }

            // Allowed transitions to paid: pending, failed, cancelled (e.g. late payment)
            if (! in_array($lockedOrder->status, ['pending', 'failed', 'cancelled'], true)) {
                return false;
            }

            $lockedOrder->status = 'paid';
            $lockedOrder->save();
            $order->status = 'paid';

            $lockedOrder->loadMissing('items.course', 'user');
            $user = $lockedOrder->user;

            if ($user) {
                foreach ($lockedOrder->items as $item) {
                    $courseId = $item->course_id;
                    $isAlreadyEnrolled = $user->enrollments()->where('courses.id', $courseId)->exists();

                    $user->enrollments()->syncWithoutDetaching([
                        $courseId => ['enrolled_at' => now()],
                    ]);

                    if (! $isAlreadyEnrolled && $item->course) {
                        PointService::addPoints($user, $item->course, 'purchase');
                    }
                }

                if ($lockedOrder->coupon_id) {
                    $coupon = Coupon::find($lockedOrder->coupon_id);
                    if ($coupon) {
                        $coupon->increment('uses_count');

                        $userCoupon = $user->coupons()->where('coupon_id', $coupon->id)->first();
                        if ($userCoupon) {
                            $userCoupon->pivot->increment('uses_count');
                        } else {
                            $user->coupons()->attach($coupon->id, ['uses_count' => 1]);
                        }
                    }
                }

                BadgeService::queueFor($user);

                $courseIds = $lockedOrder->items->pluck('course_id');
                if ($courseIds->isNotEmpty()) {
                    $user->carts()->whereIn('course_id', $courseIds)->delete();
                }
            }

            return true;
        });
    }

    /**
     * Mark a pending order as failed or cancelled.
     */
    public function markFailed(Order $order, string $status): bool
    {
        return DB::transaction(function () use ($order, $status) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $lockedOrder || $lockedOrder->status !== 'pending') {
                return false;
            }

            // Valid status values in orders table are 'failed' and 'cancelled'
            $newStatus = in_array($status, ['failed', 'cancelled'], true) ? $status : 'failed';

            $lockedOrder->status = $newStatus;
            $lockedOrder->save();
            $order->status = $newStatus;

            return true;
        });
    }

    /**
     * Apply gateway status payload to order.
     *
     * @return 'paid'|'failed'|'ignored'|'amount_mismatch'
     */
    public function applyGatewayStatus(Order $order, array $status): string
    {
        $payloadGrossAmount = (int) round((float) ($status['gross_amount'] ?? 0));
        $orderFinalAmount = (int) round((float) $order->final_amount);

        if ($payloadGrossAmount !== $orderFinalAmount) {
            return 'amount_mismatch';
        }

        $transactionStatus = $status['transaction_status'] ?? '';
        $fraudStatus = $status['fraud_status'] ?? '';

        if (
            ($transactionStatus === 'capture' && $fraudStatus === 'accept')
            || $transactionStatus === 'settlement'
        ) {
            $this->markPaid($order);

            return 'paid';
        }

        if (in_array($transactionStatus, ['deny', 'cancel', 'failure', 'expire'], true)) {
            $this->markFailed($order, 'failed');

            return 'failed';
        }

        return 'ignored';
    }
}
