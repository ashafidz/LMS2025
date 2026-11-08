<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\ListQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryController extends Controller
{
    /**
     * Menampilkan halaman riwayat transaksi milik pengguna.
     */
    public function index()
    {
        $orders = ListQuery::applySearch(Auth::user()->orders()->with('items.course'), ['order_code', 'items.course:title'])
            ->latest()
            ->paginate(ListQuery::perPage(10, [10, 25, 50]))
            ->withQueryString();

        return view('student.transactions.index', compact('orders'));
    }

    /**
     * Membuat dan mengunduh invoice dalam format PDF.
     */
    public function downloadInvoice(Order $order)
    {

        $order->load('user', 'items.course', 'coupon');

        // DIPERBARUI: Menggunakan view 'invoice_pdf.blade.php' yang baru
        $pdf = Pdf::loadView('student.transactions.invoice_pdf', compact('order'));

        $safeOrderCode = str_replace('/', '-', $order->order_code);
        $fileName = 'invoice-'.$safeOrderCode.'.pdf';

        // Kembalikan PDF sebagai respons unduhan di browser
        return $pdf->download($fileName);
    }
}
