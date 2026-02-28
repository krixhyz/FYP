<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('location_text')->nullable()->after('location');
            $table->string('city')->nullable()->after('location_text');
            $table->decimal('latitude', 10, 7)->nullable()->after('city');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('place_id')->nullable()->after('longitude');
            $table->enum('location_precision', ['approx', 'exact'])->default('approx')->after('place_id');

            $table->index(['status', 'city', 'created_at'], 'products_status_city_created_idx');
            $table->index(['status', 'latitude', 'longitude', 'created_at'], 'products_status_lat_lng_created_idx');
        });

        DB::statement('UPDATE products SET location_text = location WHERE location_text IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_city_created_idx');
            $table->dropIndex('products_status_lat_lng_created_idx');

            $table->dropColumn([
                'location_text',
                'city',
                'latitude',
                'longitude',
                'place_id',
                'location_precision',
            ]);
        });
    }
};
