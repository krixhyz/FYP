<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'province_id')) {
                $table->foreignId('province_id')->nullable()->after('email')->constrained('provinces')->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'city_id')) {
                $table->foreignId('city_id')->nullable()->after('province_id')->constrained('cities')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'city_id')) {
                $table->dropConstrainedForeignId('city_id');
            }
            if (Schema::hasColumn('users', 'province_id')) {
                $table->dropConstrainedForeignId('province_id');
            }
        });
    }
};
