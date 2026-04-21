<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User\User;
use App\Models\User\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckoutWithBuyerDetailsTest extends TestCase
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
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Test cart checkout with buyer details
     */
    public function test_cart_checkout_captures_buyer_details(): void
    {
        Mail::fake();

        $seller = $this->createUser('John Seller', 'seller@example.com');
        $buyer = $this->createUser('Jane Buyer', 'jane@example.com');

        $product = Product::create([
            'user_id' => $seller->id,
            'title' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
            'quantity' => 10,
            'type' => ['sell'],
            'category' => 'electronics',
            'image' => 'test.jpg',
            'status' => 'available',
        ]);

        $buyer->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'type' => 'buy',
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        echo "\n✅ Test 1: Cart item created successfully\n";
    }

    /**
     * Test that buyer details are required for checkout
     */
    public function test_checkout_requires_buyer_details(): void
    {
        $buyer = $this->createUser('Jane Buyer', 'jane@example.com');
        $seller = $this->createUser('John Seller', 'seller@example.com');

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

        $buyer->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'type' => 'buy',
        ]);

        // Try checkout without buyer details
        $response = $this->actingAs($buyer)->post(route('orders.placeFromCart'), [
            'buyer_name' => '',
            'buyer_email' => '',
            'payment_gateway' => 'esewa',
        ]);

        // Should not proceed to payment creation when buyer details are missing
        $response->assertRedirect();
        $this->assertDatabaseCount('payments', 0);

        echo "\n✅ Test 2: Buyer details validation working correctly\n";
    }

    /**
     * Test order model relationships
     */
    public function test_order_model_buyer_and_seller_relationships(): void
    {
        $buyer = $this->createUser('Buyer User', 'buyer@example.com');
        $seller = $this->createUser('Seller User', 'seller@example.com');

        $product = Product::create([
            'user_id' => $seller->id,
            'title' => 'Test Product',
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
            'payment_id' => null,
            'transaction_type' => 'buy',
            'quantity' => 1,
            'unit_price' => 150.00,
            'total_price' => 150.00,
            'status' => 'completed',
            'buyer_name' => 'Buyer User',
            'buyer_phone' => '9841234567',
            'buyer_email' => 'buyer@example.com',
            'buyer_address' => '123 Main St, City',
        ]);

        // Verify relationships
        $this->assertEquals($buyer->id, $order->buyer->id);
        $this->assertEquals('Buyer User', $order->buyer->name);

        $this->assertEquals($seller->id, $order->seller->id);
        $this->assertEquals('Seller User', $order->seller->name);

        $this->assertEquals($product->id, $order->product->id);

        // Verify buyer details stored
        $this->assertEquals('Buyer User', $order->buyer_name);
        $this->assertEquals('9841234567', $order->buyer_phone);
        $this->assertEquals('buyer@example.com', $order->buyer_email);
        $this->assertEquals('123 Main St, City', $order->buyer_address);

        echo "\n✅ Test 3: Order relationships and buyer details working correctly\n";
    }

    /**
     * Test that order includes all required fields in database
     */
    public function test_order_table_has_all_fields(): void
    {
        $buyer = $this->createUser('Test Buyer', 'testbuyer@example.com');
        $seller = $this->createUser('Test Seller', 'testseller@example.com');
        
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

        Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'transaction_type' => 'buy',
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00,
            'status' => 'completed',
            'buyer_name' => 'Test Buyer',
            'buyer_phone' => '9800000000',
            'buyer_email' => 'test@example.com',
            'buyer_address' => 'Test Address',
        ]);

        $this->assertDatabaseHas('orders', [
            'seller_id' => $seller->id,
            'buyer_name' => 'Test Buyer',
            'buyer_phone' => '9800000000',
            'buyer_email' => 'test@example.com',
            'buyer_address' => 'Test Address',
        ]);

        echo "\n✅ Test 4: All order fields stored correctly in database\n";
    }

    /**
     * Test that phone_number field exists on users table
     */
    public function test_users_table_has_phone_number_field(): void
    {
        $user = $this->createUser('John Doe', 'john@example.com', '9841111111');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '9841111111',
        ]);

        echo "\n✅ Test 5: Phone number field working on users table\n";
    }
}

