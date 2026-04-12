<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Remove redundant location columns - users already have province_id and city_id
            $columnsToRemove = [];
            if (Schema::hasColumn('products', 'location_text')) $columnsToRemove[] = 'location_text';
            if (Schema::hasColumn('products', 'city')) $columnsToRemove[] = 'city';
            if (Schema::hasColumn('products', 'latitude')) $columnsToRemove[] = 'latitude';
            if (Schema::hasColumn('products', 'longitude')) $columnsToRemove[] = 'longitude';
            if (Schema::hasColumn('products', 'place_id')) $columnsToRemove[] = 'place_id';
            if (Schema::hasColumn('products', 'location_precision')) $columnsToRemove[] = 'location_precision';
            
            if (count($columnsToRemove) > 0) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('location_text')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('place_id')->nullable();
            $table->string('location_precision')->nullable();
        });
    }
};
