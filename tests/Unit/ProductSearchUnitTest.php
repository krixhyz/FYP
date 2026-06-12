<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_only_matching_approved_products(): void
    {
        $owner = User::factory()->create();

        $this->makeProduct($owner->id, ['title' => 'Vintage Jacket', 'approval_status' => 'APPROVED']);
        $this->makeProduct($owner->id, ['title' => 'Gaming Laptop', 'approval_status' => 'APPROVED']);
        $this->makeProduct($owner->id, ['title' => 'Pending Jacket', 'approval_status' => 'PENDING']);

        $results = Product::query()
            ->where('status', 'available')
            ->where('approval_status', 'APPROVED')
            ->where(function ($query) {
                $query->where('title', 'like', '%Jacket%')
                    ->orWhere('description', 'like', '%Jacket%');
            })
            ->pluck('title')
            ->all();

        $this->assertSame(['Vintage Jacket'], $results);
    }

    private function makeProduct(int $ownerId, array $overrides = []): Product
    {
        $category = Category::firstOrCreate(
            ['name' => 'General', 'parent_id' => null],
            [
                'base_co2_kg' => 1.00,
                'reuse_pct' => 50.00,
                'eco_points' => 10.00,
            ]
        );

        return Product::create(array_merge([
            'user_id' => $ownerId,
            'title' => 'Sample Product',
            'description' => 'Sample description',
            'price' => 1000,
            'quantity' => 2,
            'type' => ['sell'],
            'category_id' => $category->id,
            'condition' => 'GOOD',
            'status' => 'available',
            'approval_status' => 'APPROVED',
        ], $overrides));
    }
}
