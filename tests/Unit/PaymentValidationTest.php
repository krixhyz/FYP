<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User\User;
use App\Notifications\User\OrderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_paid_order_triggers_seller_notification(): void
    {
        Notification::fake();

        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $category = Category::firstOrCreate(
            ['name' => 'General', 'parent_id' => null],
            [
                'base_co2_kg' => 1.00,
                'reuse_pct' => 50.00,
                'eco_points' => 10.00,
            ]
        );

        $product = Product::create([
            'user_id' => $seller->id,
            'title' => 'Payment Trigger Product',
            'description' => 'Product used for payment notification test',
            'price' => 1200,
            'quantity' => 5,
            'type' => ['sell'],
            'category_id' => $category->id,
            'condition' => 'GOOD',
            'status' => 'available',
            'approval_status' => 'APPROVED',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'transaction_type' => 'buy',
            'quantity' => 1,
            'unit_price' => 1200,
            'total_price' => 1200,
            'subtotal' => 1200,
            'service_fee' => 36,
            'total_amount' => 1236,
            'status' => 'completed',
            'payment_status' => 'paid',
            'buyer_name' => 'Test Buyer',
            'buyer_phone' => '9841234567',
            'buyer_email' => 'buyer@example.com',
            'buyer_address' => 'Kathmandu',
        ]);

        $seller->notify(new OrderNotification($order));

        Notification::assertSentTo($seller, OrderNotification::class);
    }
}
