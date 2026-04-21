<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'default_location_text',
            'default_city',
            'default_latitude',
            'default_longitude',
            'default_place_id',
        ];

        $existingColumns = array_values(array_filter($columns, static fn (string $column): bool => Schema::hasColumn('users', $column)));

        if (!empty($existingColumns)) {
            Schema::table('users', function (Blueprint $table) use ($existingColumns): void {
                $table->dropColumn($existingColumns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'default_location_text')) {
                $table->string('default_location_text')->nullable();
            }

            if (!Schema::hasColumn('users', 'default_city')) {
                $table->string('default_city')->nullable();
            }

            if (!Schema::hasColumn('users', 'default_latitude')) {
                $table->decimal('default_latitude', 10, 7)->nullable();
            }

            if (!Schema::hasColumn('users', 'default_longitude')) {
                $table->decimal('default_longitude', 10, 7)->nullable();
            }

            if (!Schema::hasColumn('users', 'default_place_id')) {
                $table->string('default_place_id')->nullable();
            }
        });
    }
};
