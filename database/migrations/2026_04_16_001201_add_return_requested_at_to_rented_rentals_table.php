<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rented_rentals', function (Blueprint $table) {
            if (!Schema::hasColumn('rented_rentals', 'return_requested_at')) {
                $table->dateTime('return_requested_at')->nullable()->after('returned_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rented_rentals', function (Blueprint $table) {
            if (Schema::hasColumn('rented_rentals', 'return_requested_at')) {
                $table->dropColumn('return_requested_at');
            }
        });
    }
};