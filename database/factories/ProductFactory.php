<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Product; 

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
   protected $model = Product::class;
   
    public function definition(): array
{
        $categoryId = Category::query()->value('id');

        if (!$categoryId) {
            $categoryId = Category::create([
                'name' => 'General',
                'parent_id' => null,
                'base_co2_kg' => 1.00,
                'reuse_pct' => 50.00,
                'eco_points' => 10.00,
            ])->id;
        }

    return [
        'user_id' => 1,
        'title' => fake()->sentence(3),
        'description' => fake()->paragraph(),
        'price' => fake()->randomFloat(2, 10, 500),
        'type' => $this->faker->randomElements(['sell', 'rent', 'swap'], rand(1, 3)),
            'category_id' => $categoryId,
            'condition' => fake()->randomElement(['NEW', 'LIKE_NEW', 'GOOD', 'FAIR', 'WORN_FOR_PARTS']),
            'approval_status' => 'APPROVED',
            'status' => 'available',
            'quantity' => fake()->numberBetween(1, 10),
        'image' => 'default.jpg',
    ];
}

}
