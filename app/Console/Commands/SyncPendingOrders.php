<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderSettlementService;
use App\Services\XenditService;
use Illuminate\Console\Command;

class SyncPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:sync-pending {--minutes=15} {--hours=48}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek status order pending ke Xendit dan selesaikan yang sudah dibayar';

    /**
     * Execute the console command.
     */
    public function handle(XenditService $xendit, OrderSettlementService $settlementService)
    {
        $minutes = (int) $this->option('minutes');
        $hours = (int) $this->option('hours');

        $query = Order::query()
            ->where('status', 'pending')
            ->where('final_amount', '>', 0)
            ->whereNotNull('xendit_invoice_id')
            ->whereBetween('created_at', [
                now()->subHours($hours),
                now()->subMinutes($minutes),
            ]);

        $checked = 0;
        $paid = 0;
        $failed = 0;
        $unchanged = 0;

        $query->chunkById(100, function ($orders) use ($xendit, $settlementService, &$checked, &$paid, &$failed, &$unchanged) {
            foreach ($orders as $order) {
                $checked++;
                $status = $xendit->fetchStatus($order);
                if (empty($status)) {
                    $unchanged++;

                    continue;
                }

                $result = $settlementService->applyGatewayStatus($order, $status);

                if ($result === 'paid') {
                    $paid++;
                } elseif ($result === 'failed') {
                    $failed++;
                } else {
                    $unchanged++;
                }
            }
        });

        $this->info("Checked: {$checked}, paid: {$paid}, failed: {$failed}, unchanged: {$unchanged}");

        return self::SUCCESS;
    }
}
