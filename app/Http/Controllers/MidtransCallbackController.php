<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransCallbackController extends Controller
{
    /**
     * Menangani notifikasi dari Midtrans.
     */
    public function handle(Request $request)
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        try {
            // Buat instance dari notifikasi Midtrans
            $notification = new Notification();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        // Ambil order_id dan status transaksi dari notifikasi
        $orderCode = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status;

        // Cari pesanan di database berdasarkan order_code
        $order = Order::where('order_code', $orderCode)->first();

        // Verifikasi signature key
        $signature_key = hash('sha512', $orderCode . $notification->status_code . $notification->gross_amount . config('midtrans.server_key'));
        if ($notification->signature_key != $signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        if ($transactionStatus == 'capture') {
            // Untuk transaksi kartu kredit
            if ($fraudStatus == 'accept') {
                $this->updateOrderStatus($order, 'paid');
            }
        } elseif ($transactionStatus == 'settlement') {
            // Untuk metode pembayaran lain
            $this->updateOrderStatus($order, 'paid');
        } elseif ($transactionStatus == 'pending') {
            // Menunggu pembayaran
            $this->updateOrderStatus($order, 'pending');
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            // Pembayaran gagal atau dibatalkan
            $this->updateOrderStatus($order, 'failed');
        }

        return response()->json(['message' => 'Notification handled']);
    }

    /**
     * Helper method untuk memperbarui status pesanan dan mendaftarkan siswa.
     */
    protected function updateOrderStatus(Order $order, string $newStatus)
    {
        // Hanya proses jika status berubah
        if ($order->status === 'pending') {
            $order->status = $newStatus;
            $order->save();

            // Jika pembayaran berhasil, daftarkan siswa ke kursus
            if ($newStatus === 'paid') {
                $this->enrollStudent($order);
            }
        }
    }

    /**
     * Helper method untuk mendaftarkan siswa ke kursus setelah pembayaran berhasil.
     */
    protected function enrollStudent(Order $order)
    {
        $order->load('items.course', 'user');

        foreach ($order->items as $item) {
            // Buat record baru di tabel pivot 'course_enrollments'
            $order->user->enrollments()->attach($item->course_id, ['enrolled_at' => now()]);

            // Di sini Anda juga bisa menambahkan logika lain,
            // seperti mengirim email konfirmasi pendaftaran ke siswa.
        }
    }


    public function success($custom_order_id)
    {
        // * ambil data order
        $order = Order::where('custom_order_id', $custom_order_id)->first();

        // // * ambil data order detail
        // $orderDetails = OrderDetail::where('order_id', $custom_order_id)->get();

        // * hapus cart
        Cart::where('user_id', Auth::user()->id)->delete();

        // rubah status order menjadi "Menunggu Konfirmasi"
        $order->status = 'Menunggu Verifikasi';
        $order->save();

        return view('costumer.checkout.success', compact('order'));
    }


    public function fail($custom_order_id)
    {
        // * ambil data order
        $order = Order::where('custom_order_id', $custom_order_id)->first();

        // rubah status order menjadi "Gagal"
        $order->status = 'Dibatalkan';
        $order->save();

        return view('costumer.checkout.fail', compact('order'));
    }
    public function pending($custom_order_id)
    {
        // * ambil data order
        $order = Order::where('custom_order_id', $custom_order_id)->first();

        // rubah status order menjadi "Menunggu Pembayaran"
        $order->status = 'Menunggu Pembayaran';
        $order->save();

        // return view('costumer.checkout.pending', compact('order'));
        return redirect()->route('costumer.order.index')->with('success', 'Pesanan anda sedang menunggu pembayaran');
    }

    public function pendingPayment($custom_order_id)
    {
        // * ambil data order
        $order = Order::where('custom_order_id', $custom_order_id)->first();

        if (!$order) {
            abort(404, 'Order not found.');
        }

        return view('costumer.checkout.pending', compact('order'));
    }

    public function cancel($custom_order_id)
    {
        // * ambil data order
        $order = Order::where('custom_order_id', $custom_order_id)->first();

        // rubah status order menjadi "Dibatalkan"
        $order->status = 'Dibatalkan';
        $order->save();

        return view('costumer.checkout.cancel', compact('order'));
    }
}
