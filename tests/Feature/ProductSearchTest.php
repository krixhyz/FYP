<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_search_products_by_keyword(): void
    {
        $owner = User::factory()->create();

        $this->makeProduct($owner->id, [
            'title' => 'Vintage Jacket',
            'description' => 'A warm winter jacket',
            'category' => 'clothing',
            'type' => ['sell'],
            'price' => 1500,
        ]);

        $this->makeProduct($owner->id, [
            'title' => 'Gaming Laptop',
            'description' => 'High performance machine',
            'category' => 'electronics',
            'type' => ['sell'],
            'price' => 65000,
        ]);

        $response = $this->get(route('products.index', ['search' => 'jacket']));

        $response->assertOk();
        $response->assertSee('Vintage Jacket');
        $response->assertDontSee('Gaming Laptop');
    }

    public function test_authenticated_user_does_not_see_own_products_in_results(): void
    {
        $user = User::factory()->create();
        $otherOwner = User::factory()->create();

        $this->makeProduct($user->id, [
            'title' => 'My Searchable Item',
            'description' => 'Should be excluded from my own feed',
            'category' => 'general',
            'type' => ['sell'],
            'price' => 100,
        ]);

        $this->makeProduct($otherOwner->id, [
            'title' => 'Public Searchable Item',
            'description' => 'Should be visible',
            'category' => 'general',
            'type' => ['sell'],
            'price' => 120,
        ]);

        $response = $this->actingAs($user)->get(route('products.index', ['search' => 'searchable']));

        $response->assertOk();
        $response->assertSee('Public Searchable Item');
        $response->assertDontSee('My Searchable Item');
    }

    public function test_can_filter_by_category_type_and_price_range(): void
    {
        $owner = User::factory()->create();

        $this->makeProduct($owner->id, [
            'title' => 'Low Cost Chair',
            'description' => 'Furniture sale',
            'category' => 'furniture',
            'type' => ['sell'],
            'price' => 800,
        ]);

        $this->makeProduct($owner->id, [
            'title' => 'Premium Sofa',
            'description' => 'Furniture sale',
            'category' => 'furniture',
            'type' => ['sell'],
            'price' => 12000,
        ]);

        $this->makeProduct($owner->id, [
            'title' => 'Furniture Rental Set',
            'description' => 'Furniture for rent',
            'category' => 'furniture',
            'type' => ['rent'],
            'price' => 5000,
        ]);

        $response = $this->get(route('products.index', [
            'category' => 'furniture',
            'listing_type' => 'sell',
            'min_price' => 500,
            'max_price' => 2000,
        ]));

        $response->assertOk();
        $response->assertSee('Low Cost Chair');
        $response->assertDontSee('Premium Sofa');
        $response->assertDontSee('Furniture Rental Set');
    }

    private function makeProduct(int $ownerId, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'user_id' => $ownerId,
            'title' => 'Sample Product',
            'description' => 'Sample description',
            'price' => 1000,
            'quantity' => 3,
            'type' => ['sell'],
            'category' => 'general',
            'status' => 'available',
        ], $overrides));
    }
}
