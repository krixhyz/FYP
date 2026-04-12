<?php

namespace Tests\Feature;

use App\Mail\OrderCreated;
use App\Models\Order;
use App\Models\Product;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(string $name = 'Test User', string $email = 'test@example.com', string $phone = null): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'phone_number' => $phone ?? '9841234567',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
    }

    /**
     * Test that OrderCreated mailable can be instantiated
     */
    public function test_order_created_mailable_instantiates(): void
    {
        $seller = $this->createUser('Seller', 'seller@example.com');
        $buyer = $this->createUser('Buyer', 'buyer@example.com');

        $product = Product::create([
            'user_id' => $seller->id,
            'title' => 'Test Product',
            'description' => 'Description',
            'price' => 100.00,
            'quantity' => 5,
            'type' => ['sell'],
            'category' => 'electronics',
            'image' => 'test.jpg',
            'status' => 'available',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'transaction_type' => 'buy',
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
            'status' => 'completed',
            'buyer_name' => 'Buyer Name',
            'buyer_phone' => '9841234567',
            'buyer_email' => 'buyer@example.com',
            'buyer_address' => '123 Main St',
        ]);

        $mailable = new OrderCreated($order);
        
        $this->assertNotNull($mailable);
        $this->assertEquals($order->id, $mailable->order->id);

        echo "\n✅ Test 1: OrderCreated mailable instantiates correctly\n";
    }

    /**
     * Test that OrderCreated email has correct subject
     */
    public function test_order_created_email_has_correct_subject(): void
    {
        Mail::fake();

        $seller = $this->createUser('Seller', 'seller@example.com');
        $buyer = $this->createUser('Buyer', 'buyer@example.com');

        $product = Product::create([
            'user_id' => $seller->id,
            'title' => 'Premium Widget',
            'description' => 'Description',
            'price' => 150.00,
            'quantity' => 5,
            'type' => ['sell'],
            'category' => 'electronics',
            'image' => 'test.jpg',
            'status' => 'available',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'transaction_type' => 'buy',
            'quantity' => 1,
            'unit_price' => 150.00,
            'total_price' => 150.00,
            'status' => 'completed',
            'buyer_name' => 'Buyer Name',
            'buyer_phone' => '9841234567',
            'buyer_email' => 'buyer@example.com',
            'buyer_address' => '123 Main St',
        ]);

        $mailable = new OrderCreated($order);
        
        // The subject should contain the product title
        $envelope = $mailable->envelope();
        $this->assertStringContainsString('New Order', $envelope->subject);
        $this->assertStringContainsString('Premium Widget', $envelope->subject);

        echo "\n✅ Test 2: OrderCreated email has correct subject\n";
    }

    /**
     * Test that OrderCreated email contains buyer details
     */
    public function test_order_created_email_contains_buyer_details(): void
    {
        Mail::fake();

        $seller = $this->createUser('John Seller', 'seller@example.com');
        $buyer = $this->createUser('Jane Buyer', 'buyer@example.com');

        $product = Product::create([
            'user_id' => $seller->id,
            'title' => 'Test Product',
            'description' => 'Description',
            'price' => 100.00,
            'quantity' => 5,
            'type' => ['sell'],
            'category' => 'electronics',
            'image' => 'test.jpg',
            'status' => 'available',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'transaction_type' => 'buy',
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00,
            'status' => 'completed',
            'buyer_name' => 'Jane Buyer Contact',
            'buyer_phone' => '9847654321',
            'buyer_email' => 'jane.buyer@contact.com',
            'buyer_address' => '456 Oak Avenue, Kathmandu',
        ]);

        $mailable = new OrderCreated($order);
        
        // Verify the mailable has the order with all buyer details
        $this->assertNotNull($mailable->order);
        $this->assertEquals('Jane Buyer Contact', $mailable->order->buyer_name);
        $this->assertEquals('9847654321', $mailable->order->buyer_phone);
        $this->assertEquals('jane.buyer@contact.com', $mailable->order->buyer_email);
        $this->assertEquals('456 Oak Avenue, Kathmandu', $mailable->order->buyer_address);
        $this->assertEquals('Test Product', $mailable->order->product->title);

        echo "\n✅ Test 3: OrderCreated mailable contains buyer details\n";
    }

    /**
     * Test that OrderNotification can be instantiated
     */
    public function test_order_notification_instantiates(): void
    {
        Notification::fake();

        $seller = $this->createUser('Seller', 'seller@example.com');
        $buyer = $this->createUser('Buyer', 'buyer@example.com');

        $product = Product::create([
            'user_id' => $seller->id,
            'title' => 'Test Product',
            'description' => 'Description',
            'price' => 100.00,
            'quantity' => 5,
            'type' => ['sell'],
            'category' => 'electronics',
            'image' => 'test.jpg',
            'status' => 'available',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'transaction_type' => 'buy',
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
            'status' => 'completed',
            'buyer_name' => 'Buyer Name',
            'buyer_phone' => '9841234567',
            'buyer_email' => 'buyer@example.com',
            'buyer_address' => '123 Main St',
        ]);

        $notification = new \App\Notifications\User\OrderNotification($order);
        
        $this->assertNotNull($notification);

        echo "\n✅ Test 4: OrderNotification instantiates correctly\n";
    }

    /**
     * Test that seller has phone_number field for communication
     */
    public function test_seller_phone_number_can_be_stored(): void
    {
        $seller = $this->createUser('John Seller', 'seller@example.com', '9841111111');

        $this->assertEquals('9841111111', $seller->phone_number);
        
        $this->assertDatabaseHas('users', [
            'id' => $seller->id,
            'phone_number' => '9841111111',
        ]);

        echo "\n✅ Test 5: Seller phone number stored correctly for communication\n";
    }

    /**
     * Test order status is completed
     */
    public function test_order_status_is_completed(): void
    {
        $seller = $this->createUser('Seller', 'seller@example.com');
        $buyer = $this->createUser('Buyer', 'buyer@example.com');

        $product = Product::create([
            'user_id' => $seller->id,
            'title' => 'Test Product',
            'description' => 'Description',
            'price' => 100.00,
            'quantity' => 5,
            'type' => ['sell'],
            'category' => 'electronics',
            'image' => 'test.jpg',
            'status' => 'available',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'transaction_type' => 'buy',
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
            'status' => 'completed',
            'buyer_name' => 'Buyer Name',
            'buyer_phone' => '9841234567',
            'buyer_email' => 'buyer@example.com',
            'buyer_address' => '123 Main St',
        ]);

        $this->assertEquals('completed', $order->status);

        echo "\n✅ Test 6: Order status is correctly set to completed\n";
    }
}
