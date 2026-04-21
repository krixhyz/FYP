<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // Parent Categories
        $clothing_id = Category::create([
            'name' => 'Clothing',
            'parent_id' => null,
            'base_co2_kg' => 0,
            'reuse_pct' => 0,
            'eco_points' => 0,
        ])->id;

        $electronics_id = Category::create([
            'name' => 'Electronics',
            'parent_id' => null,
            'base_co2_kg' => 0,
            'reuse_pct' => 0,
            'eco_points' => 0,
        ])->id;

        $furniture_id = Category::create([
            'name' => 'Furniture',
            'parent_id' => null,
            'base_co2_kg' => 0,
            'reuse_pct' => 0,
            'eco_points' => 0,
        ])->id;

        $books_id = Category::create([
            'name' => 'Books',
            'parent_id' => null,
            'base_co2_kg' => 0,
            'reuse_pct' => 0,
            'eco_points' => 0,
        ])->id;

        $toys_id = Category::create([
            'name' => 'Toys & Games',
            'parent_id' => null,
            'base_co2_kg' => 0,
            'reuse_pct' => 0,
            'eco_points' => 0,
        ])->id;

        $appliances_id = Category::create([
            'name' => 'Home Appliances',
            'parent_id' => null,
            'base_co2_kg' => 0,
            'reuse_pct' => 0,
            'eco_points' => 0,
        ])->id;

        $sports_id = Category::create([
            'name' => 'Sports Equipment',
            'parent_id' => null,
            'base_co2_kg' => 0,
            'reuse_pct' => 0,
            'eco_points' => 0,
        ])->id;

        // Clothing Subcategories
        Category::create(['name' => 'Tops & T-Shirts', 'parent_id' => $clothing_id, 'base_co2_kg' => 7.00, 'reuse_pct' => 75, 'eco_points' => 5.25]);
        Category::create(['name' => 'Jeans & Trousers', 'parent_id' => $clothing_id, 'base_co2_kg' => 30.00, 'reuse_pct' => 75, 'eco_points' => 22.50]);
        Category::create(['name' => 'Jackets & Coats', 'parent_id' => $clothing_id, 'base_co2_kg' => 25.00, 'reuse_pct' => 75, 'eco_points' => 18.75]);
        Category::create(['name' => 'Dresses & Skirts', 'parent_id' => $clothing_id, 'base_co2_kg' => 15.00, 'reuse_pct' => 75, 'eco_points' => 11.25]);
        Category::create(['name' => 'Shoes', 'parent_id' => $clothing_id, 'base_co2_kg' => 14.00, 'reuse_pct' => 75, 'eco_points' => 10.50]);

        // Electronics Subcategories
        Category::create(['name' => 'Smartphones', 'parent_id' => $electronics_id, 'base_co2_kg' => 60.00, 'reuse_pct' => 70, 'eco_points' => 42.00]);
        Category::create(['name' => 'Tablets', 'parent_id' => $electronics_id, 'base_co2_kg' => 110.00, 'reuse_pct' => 70, 'eco_points' => 77.00]);
        Category::create(['name' => 'Laptops', 'parent_id' => $electronics_id, 'base_co2_kg' => 270.00, 'reuse_pct' => 80, 'eco_points' => 216.00]);
        Category::create(['name' => 'Desktop PCs', 'parent_id' => $electronics_id, 'base_co2_kg' => 300.00, 'reuse_pct' => 80, 'eco_points' => 240.00]);
        Category::create(['name' => 'Cameras', 'parent_id' => $electronics_id, 'base_co2_kg' => 80.00, 'reuse_pct' => 75, 'eco_points' => 60.00]);

        // Furniture Subcategories
        Category::create(['name' => 'Chairs', 'parent_id' => $furniture_id, 'base_co2_kg' => 16.00, 'reuse_pct' => 82, 'eco_points' => 13.10]);
        Category::create(['name' => 'Tables & Desks', 'parent_id' => $furniture_id, 'base_co2_kg' => 70.00, 'reuse_pct' => 82, 'eco_points' => 57.40]);
        Category::create(['name' => 'Sofas & Couches', 'parent_id' => $furniture_id, 'base_co2_kg' => 105.00, 'reuse_pct' => 82, 'eco_points' => 86.10]);
        Category::create(['name' => 'Storage & Shelving', 'parent_id' => $furniture_id, 'base_co2_kg' => 50.00, 'reuse_pct' => 82, 'eco_points' => 41.00]);

        // Books Subcategories
        Category::create(['name' => 'Books', 'parent_id' => $books_id, 'base_co2_kg' => 5.00, 'reuse_pct' => 87, 'eco_points' => 4.35]);

        // Toys & Games Subcategories
        Category::create(['name' => 'Plastic Toys', 'parent_id' => $toys_id, 'base_co2_kg' => 6.00, 'reuse_pct' => 75, 'eco_points' => 4.50]);
        Category::create(['name' => 'Wooden Toys', 'parent_id' => $toys_id, 'base_co2_kg' => 3.00, 'reuse_pct' => 80, 'eco_points' => 2.40]);
        Category::create(['name' => 'Board Games', 'parent_id' => $toys_id, 'base_co2_kg' => 3.00, 'reuse_pct' => 80, 'eco_points' => 2.40]);

        // Home Appliances Subcategories
        Category::create(['name' => 'Washing Machines', 'parent_id' => $appliances_id, 'base_co2_kg' => 350.00, 'reuse_pct' => 25, 'eco_points' => 87.50]);
        Category::create(['name' => 'Refrigerators', 'parent_id' => $appliances_id, 'base_co2_kg' => 350.00, 'reuse_pct' => 25, 'eco_points' => 87.50]);
        Category::create(['name' => 'Microwaves', 'parent_id' => $appliances_id, 'base_co2_kg' => 80.00, 'reuse_pct' => 25, 'eco_points' => 20.00]);

        // Sports Equipment Subcategories
        Category::create(['name' => 'Bicycles', 'parent_id' => $sports_id, 'base_co2_kg' => 95.00, 'reuse_pct' => 80, 'eco_points' => 76.00]);
        Category::create(['name' => 'Gym Equipment', 'parent_id' => $sports_id, 'base_co2_kg' => 10.00, 'reuse_pct' => 75, 'eco_points' => 7.50]);
        Category::create(['name' => 'Outdoor Gear', 'parent_id' => $sports_id, 'base_co2_kg' => 8.00, 'reuse_pct' => 75, 'eco_points' => 6.00]);
    }
}
