<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rented_rentals', function (Blueprint $table) {
            if (!Schema::hasColumn('rented_rentals', 'returned_at')) {
                $table->dateTime('returned_at')->nullable()->after('end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rented_rentals', function (Blueprint $table) {
            if (Schema::hasColumn('rented_rentals', 'returned_at')) {
                $table->dropColumn('returned_at');
            }
        });
    }
};
