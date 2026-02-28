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
        Schema::table('users', function (Blueprint $table) {
            $table->string('default_location_text')->nullable()->after('role');
            $table->string('default_city')->nullable()->after('default_location_text');
            $table->decimal('default_latitude', 10, 7)->nullable()->after('default_city');
            $table->decimal('default_longitude', 10, 7)->nullable()->after('default_latitude');
            $table->string('default_place_id')->nullable()->after('default_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'default_location_text',
                'default_city',
                'default_latitude',
                'default_longitude',
                'default_place_id',
            ]);
        });
    }
};
