<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;

class DebugBuyerDetails extends Command
{
    protected $signature = 'debug:buyer-details {--limit=5}';
    protected $description = 'Debug buyer details in orders and payments';

    public function handle()
    {
        $this->info('=== Latest Orders ===');
        $orders = Order::with('product', 'buyer')->latest()->limit($this->option('limit'))->get();

        if ($orders->isEmpty()) {
            $this->warn('No orders found');
            return;
        }

        foreach ($orders as $order) {
            $this->line("\n📦 Order #{$order->id}");
            $this->table(['Field', 'Value'], [
                ['Product', $order->product?->title ?? 'N/A'],
                ['Buyer Name', $order->buyer_name ?? '(NULL)'],
                ['Buyer Phone', $order->buyer_phone ?? '(NULL)'],
                ['Buyer Email', $order->buyer_email ?? '(NULL)'],
                ['Buyer Address', substr($order->buyer_address ?? '(NULL)', 0, 50)],
                ['Status', $order->status],
                ['Created', $order->created_at->format('M d, Y H:i')],
            ]);

            // Check payment data
            $payment = $order->payment;
            if ($payment) {
                $this->line("\n💳 Payment #{$payment->id}");
                $this->table(['Field', 'Value'], [
                    ['Status', $payment->status],
                    ['Request Payload Keys', implode(', ', array_keys($payment->request_payload ?? []))],
                    ['Buyer Details', json_encode($payment->request_payload['buyer_details'] ?? [])],
                ]);
            }
        }
    }
}
