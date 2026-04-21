<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get parent category IDs
        $clothingId = DB::table('categories')
            ->where('name', 'Clothing')
            ->whereNull('parent_id')
            ->value('id');

        $electronicsId = DB::table('categories')
            ->where('name', 'Electronics')
            ->whereNull('parent_id')
            ->value('id');

        $furnitureId = DB::table('categories')
            ->where('name', 'Furniture')
            ->whereNull('parent_id')
            ->value('id');

        // Map old string categories to new IDs
        DB::table('products')
            ->where('category', 'clothing')
            ->update(['category_id' => $clothingId]);

        DB::table('products')
            ->where('category', 'electronics')
            ->update(['category_id' => $electronicsId]);

        DB::table('products')
            ->where('category', 'furniture')
            ->update(['category_id' => $furnitureId]);

        // Fallback: 'general' → Clothing
        DB::table('products')
            ->where('category', 'general')
            ->update(['category_id' => $clothingId]);

        // Ensure no NULL conditions
        DB::table('products')
            ->whereNull('condition')
            ->update(['condition' => 'GOOD']);
    }

    public function down(): void
    {
        DB::table('products')->update(['category_id' => null]);
        DB::table('products')->update(['condition' => null]);
    }
};
