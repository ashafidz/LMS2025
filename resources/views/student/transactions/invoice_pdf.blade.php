<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice {{ $order->order_code }}</title>
    <style>
        body { font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; color: #333; }
        .invoice-container { width: 100%; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        .header-table td { padding: 5px 0; }
        .header-table .logo { font-size: 24px; font-weight: bold; color: #007bff; }
        .header-table .status { font-size: 20px; font-weight: bold; text-align: right; color: #28a745; padding: 5px 15px; }
        .address-table { margin-top: 20px; }
        .address-table td { width: 50%; vertical-align: top; }
        .invoice-details-table { margin-top: 20px; border-bottom: 1px solid #dee2e6; padding-bottom: 10px; }
        .invoice-details-table td { padding: 2px 0; }
        .items-table { margin-top: 20px; }
        .items-table th, .items-table td { padding: 10px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .items-table th { background-color: #f8f9fa; font-weight: bold; }
        .items-table .text-right, .summary-table .text-right { text-align: right; }
        .summary-table { margin-top: 20px; width: 50%; margin-left: 50%; }
        .summary-table td { padding: 5px; }
        .summary-table .total { font-weight: bold; border-top: 1px solid #dee2e6; font-size: 1.1em; }
        .transactions-table { margin-top: 30px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #6c757d; }
    </style>
</head>
<body>
    @php $settings = \App\Models\SiteSetting::first(); @endphp
    <div class="invoice-container">
        <table class="header-table">
            <tr>
                <td class="logo">{{ $settings->site_name ?? 'LMS Anda' }}</td>
                <td class="status">LUNAS</td>
            </tr>
        </table>
        <hr>
        <table class="address-table">
            <tr>
                <td>
                    <strong>Ditagihkan Kepada</strong><br>
                    {{ $order->user->name }}<br>
                    {{ $order->user->email }}
                </td>
                <td style="text-align: right;">
                    <strong>Dibayarkan Kepada</strong><br>
                    {{ $settings->company_name }}
                </td>
            </tr>
        </table>

        <table class="invoice-details-table">
            <tr>
                <td>Invoice ID</td>
                <td>: {{ $order->order_code }}</td>
            </tr>
            <tr>
                <td>Tanggal Dibuat</td>
                <td>: {{ $order->created_at->format('d F Y, H:i') }} WIB</td>
            </tr>
        </table>

        <h4 style="margin-top: 20px;">Deskripsi Produk</h4>
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>1</td>
                    <td>{{ $item->course->title }}</td>
                    <td>1</td>
                    <td class="text-right">Rp{{ number_format($item->price_at_purchase, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($item->price_at_purchase, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                {{-- Baris untuk rincian biaya --}}
                <tr>
                    <td></td>
                    <td colspan="3" class="">Diskon/Voucher</td>
                    <td class="text-right">-Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3" class="">PPN ({{ rtrim(rtrim($order->vat_percentage_at_purchase, '0'), '.') }}%)</td>
                    <td class="text-right">Rp{{ number_format($order->vat_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3" class="">Biaya Transaksi</td>
                    <td class="text-right">Rp{{ number_format($order->transaction_fee_amount, 0, ',', '.') }}</td>
                </tr>
                <tr style="font-weight: bold;">
                    <td colspan="4" class="text-right">Total</td>
                    <td class="text-right">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <h4 style="margin-top: 30px;">Transaksi</h4>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Tanggal Bayar</th>
                    <th>Gateway</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $order->updated_at->format('d F Y, H:i') }}</td>
                    <td>Midtrans</td>
                    <td class="text-right">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <strong>{{ $settings->company_name }}</strong><br>
            NPWP: {{ $settings->npwp }}<br>
            {{ $settings->address }}<br>
            Telepon: {{ $settings->phone }}
        </div>
    </div>
</body>
</html>