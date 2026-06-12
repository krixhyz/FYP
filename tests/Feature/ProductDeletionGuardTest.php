<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role = 'user'): User
    {
        return User::factory()->create([
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    private function makePendingOrderBlocker(Product $product, User $buyer): Order
    {
        return Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
            'transaction_type' => 'buy',
            'quantity' => 1,
            'unit_price' => (float) ($product->price ?? 0),
            'total_price' => (float) ($product->price ?? 0),
            'subtotal' => (float) ($product->price ?? 0),
            'service_fee' => 0,
            'total_amount' => (float) ($product->price ?? 0),
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    public function test_owner_cannot_delete_product_with_pending_order(): void
    {
        $owner = $this->makeUser('user');
        $buyer = $this->makeUser('user');

        $product = Product::factory()->create([
            'user_id' => $owner->id,
            'approval_status' => 'APPROVED',
            'status' => 'available',
            'quantity' => 3,
        ]);

        $this->makePendingOrderBlocker($product, $buyer);

        $response = $this->actingAs($owner)
            ->delete(route('products.destroy', $product->id));

        $response->assertRedirect(route('products.myListings'));

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_admin_cannot_delete_product_with_pending_order(): void
    {
        $admin = $this->makeUser('admin');
        $owner = $this->makeUser('user');
        $buyer = $this->makeUser('user');

        $product = Product::factory()->create([
            'user_id' => $owner->id,
            'approval_status' => 'APPROVED',
            'status' => 'available',
            'quantity' => 2,
        ]);

        $this->makePendingOrderBlocker($product, $buyer);

        $response = $this->actingAs($admin)
            ->delete(route('admin.products.delete', $product->id));

        $response->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_super_admin_can_force_delete_blocked_product_with_reason(): void
    {
        $superAdmin = $this->makeUser('super_admin');
        $owner = $this->makeUser('user');
        $buyer = $this->makeUser('user');

        $product = Product::factory()->create([
            'user_id' => $owner->id,
            'approval_status' => 'APPROVED',
            'status' => 'available',
            'quantity' => 1,
        ]);

        $this->makePendingOrderBlocker($product, $buyer);

        $response = $this->actingAs($superAdmin)
            ->delete(route('admin.products.forceDelete', $product->id), [
                'reason' => 'Emergency takedown for legal and policy compliance.',
            ]);

        $response->assertRedirect(route('admin.products'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_non_super_admin_cannot_use_force_delete(): void
    {
        $admin = $this->makeUser('admin');
        $owner = $this->makeUser('user');

        $product = Product::factory()->create([
            'user_id' => $owner->id,
            'approval_status' => 'APPROVED',
            'status' => 'available',
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.products.forceDelete', $product->id), [
                'reason' => 'Trying without permissions.',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
