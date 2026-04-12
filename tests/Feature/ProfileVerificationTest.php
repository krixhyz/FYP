<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User\User;
use App\Services\UserVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_cannot_create_listing_without_email_verification()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'profile_status' => 'UNVERIFIED',
        ]);

        $response = $this->actingAs($user)->post(route('products.store'), [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'category' => 'electronics',
            'listing_type' => ['sell'],
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_verified_email_user_with_unverified_profile_creates_pending_product()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'profile_status' => 'UNVERIFIED',
        ]);

        $response = $this->actingAs($user)->post(route('products.store'), [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'category' => 'electronics',
            'listing_type' => ['sell'],
            'price' => 100,
            'quantity' => 1,
        ]);

        $product = Product::latest()->first();
        $this->assertEquals('PENDING', $product->approval_status);
    }

    public function test_verified_profile_user_creates_approved_product()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'profile_status' => 'VERIFIED',
        ]);

        $response = $this->actingAs($user)->post(route('products.store'), [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'category' => 'electronics',
            'listing_type' => ['sell'],
            'price' => 100,
            'quantity' => 1,
        ]);

        $product = Product::latest()->first();
        $this->assertEquals('APPROVED', $product->approval_status);
    }

    public function test_unverified_user_limited_to_5_listings()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'profile_status' => 'UNVERIFIED',
        ]);

        // Create 5 products
        for ($i = 0; $i < 5; $i++) {
            Product::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        // Try to create a 6th product
        $response = $this->actingAs($user)->post(route('products.store'), [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'category' => 'electronics',
            'listing_type' => ['sell'],
            'price' => 100,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors(['listing']);
    }

    public function test_auto_verification_when_threshold_met()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'profile_status' => 'UNVERIFIED',
        ]);

        // Create 5 products
        $products = Product::factory()->count(5)->create([
            'user_id' => $user->id,
        ]);

        // Each product should have reviews with high ratings
        foreach ($products as $product) {
            $product->reviews()->create([
                'reviewer_id' => User::factory()->create()->id,
                'rating' => 5,
                'body' => 'Great product!',
            ]);
        }

        // Trigger evaluation
        $service = new UserVerificationService();
        $result = $service->evaluateUser($user);

        $user->refresh();
        $this->assertEquals('VERIFIED', $user->profile_status);
        $this->assertEquals('VERIFIED', $result);
    }

    public function test_pending_products_not_visible_in_marketplace()
    {
        $seller = User::factory()->create([
            'email_verified_at' => now(),
            'profile_status' => 'UNVERIFIED',
        ]);

        $buyer = User::factory()->create();

        Product::factory()->create([
            'user_id' => $seller->id,
            'status' => 'available',
            'approval_status' => 'PENDING',
        ]);

        $response = $this->actingAs($buyer)->get(route('products.index'));
        $response->assertDontSee('PENDING');
    }

    public function test_approved_products_visible_in_marketplace()
    {
        $seller = User::factory()->create([
            'email_verified_at' => now(),
            'profile_status' => 'VERIFIED',
        ]);

        $buyer = User::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'status' => 'available',
            'approval_status' => 'APPROVED',
            'title' => 'Visible Product',
        ]);

        $response = $this->actingAs($buyer)->get(route('products.index'));
        $response->assertSee('Visible Product');
    }

    public function test_admin_can_manually_verify_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['profile_status' => 'UNVERIFIED']);

        $response = $this->actingAs($admin)->post(
            route('admin.users.verify', $user->id)
        );

        $user->refresh();
        $this->assertEquals('VERIFIED', $user->profile_status);
    }

    public function test_admin_can_revoke_verification()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['profile_status' => 'VERIFIED']);

        $response = $this->actingAs($admin)->post(
            route('admin.users.revokeVerification', $user->id)
        );

        $user->refresh();
        $this->assertEquals('UNVERIFIED', $user->profile_status);
    }

    public function test_admin_can_approve_pending_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'approval_status' => 'PENDING',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.products.approve', $product->id)
        );

        $product->refresh();
        $this->assertEquals('APPROVED', $product->approval_status);
    }

    public function test_admin_can_reject_pending_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'approval_status' => 'PENDING',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.products.reject', $product->id),
            ['reason' => 'Violates community guidelines']
        );

        $product->refresh();
        $this->assertEquals('REJECTED', $product->approval_status);
    }
}
