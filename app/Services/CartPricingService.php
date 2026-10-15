<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\SiteSetting;
use Illuminate\Support\Collection;

class CartPricingService
{
    /**
     * @param  \Illuminate\Support\Collection  $cartItems  Cart models with ->course loaded
     * @return array{subtotal: int, discount: int, vat_percentage: float, vat_amount: int, transaction_fee: int, final_total: int, coupon: ?\App\Models\Coupon}
     */
    public function calculate(Collection $cartItems, ?Coupon $coupon, ?SiteSetting $settings): array
    {
        $subtotal = (int) round($cartItems->sum(fn ($item) => (float) ($item->course->price ?? 0)));

        $applicableCoupon = $coupon;
        $discount = 0;

        if ($coupon !== null) {
            if ($coupon->course_id === null) {
                $base = $subtotal;
            } else {
                $matchingItem = $cartItems->first(function ($item) use ($coupon) {
                    $courseId = $item->course?->id ?? $item->course_id;

                    return (int) $courseId === (int) $coupon->course_id;
                });

                if ($matchingItem !== null) {
                    $base = (int) round((float) ($matchingItem->course->price ?? 0));
                } else {
                    $applicableCoupon = null;
                    $base = 0;
                }
            }

            if ($applicableCoupon !== null && $base > 0) {
                if ($coupon->type === 'fixed') {
                    $discount = min((int) round((float) $coupon->value), $base);
                } elseif ($coupon->type === 'percent') {
                    $discount = (int) min($base, round($base * (float) $coupon->value / 100));
                }
                $discount = max(0, $discount);
            }
        }

        $afterDiscount = max(0, $subtotal - $discount);
        $vatPercentage = (float) ($settings?->vat_percentage ?? 0);

        if ($afterDiscount === 0) {
            $vatAmount = 0;
            $transactionFee = 0;
            $finalTotal = 0;
        } else {
            $vatAmount = (int) round($afterDiscount * $vatPercentage / 100);
            $beforeFee = $afterDiscount + $vatAmount;
            $transactionFeeFixed = (float) ($settings?->transaction_fee_fixed ?? 0);
            $transactionFeePercentage = (float) ($settings?->transaction_fee_percentage ?? 0);
            $transactionFee = (int) round($transactionFeeFixed + ($beforeFee * $transactionFeePercentage / 100));
            $finalTotal = $beforeFee + $transactionFee;
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'vat_percentage' => $vatPercentage,
            'vat_amount' => $vatAmount,
            'transaction_fee' => $transactionFee,
            'final_total' => $finalTotal,
            'coupon' => $applicableCoupon,
        ];
    }
}
