<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // JSON array of additional image paths; primary cover remains in `image`
            $table->json('images')->nullable()->after('image');
        });

        // Migrate any existing single image into the images array as first item
        DB::table('products')
            ->whereNotNull('image')
            ->lazyById()
            ->each(function ($product) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['images' => json_encode([$product->image])]);
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
