<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function success(Order $order)
    {
        // Pastikan hanya pemilik pesanan yang bisa mengakses
        // if ($order->user_id !== Auth::id()) {
        //     abort(403);
        // }

        // Update status pesanan menjadi 'paid' dan daftarkan siswa
        if ($order->status === 'pending') {
            $order->status = 'paid';
            $order->save();
            $this->enrollStudent($order);
        }

        return view('student.checkout.success', compact('order'));
    }

    public function pending(Order $order)
    {
        // if ($order->user_id !== auth()->id()) {
        //     abort(403);
        // }
        // Status sudah 'pending' dari awal, jadi tidak perlu diubah
        return view('student.checkout.pending', compact('order'));
    }

    public function failed(Order $order)
    {
        // if ($order->user_id !== auth()->id()) {
        //     abort(403);
        // }

        if ($order->status === 'pending') {
            $order->status = 'failed';
            $order->save();
        }

        return view('student.checkout.failed', compact('order'));
    }

    /**
     * Menangani pesanan yang dibatalkan oleh pengguna.
     */
    public function cancelled(Order $order)
    {
        // if ($order->user_id !== auth()->id()) {
        //     abort(403);
        // }

        // Ubah status menjadi 'cancelled' jika masih 'pending'
        if ($order->status === 'pending') {
            $order->status = 'cancelled';
            $order->save();
        }

        return view('student.checkout.cancelled', compact('order'));
    }


    /**
     * Helper method untuk mendaftarkan siswa ke kursus.
     */
    private function enrollStudent(Order $order)
    {
        $order->load('items.course', 'user');
        foreach ($order->items as $item) {
            $order->user->enrollments()->syncWithoutDetaching($item->course_id);
        }
    }
}
