<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            if (!Schema::hasColumn('disputes', 'rented_rental_id')) {
                $table->foreignId('rented_rental_id')->nullable()->after('swap_id')->constrained('rented_rentals')->nullOnDelete();
            }

            if (!Schema::hasColumn('disputes', 'evidence_photos')) {
                $table->json('evidence_photos')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            if (Schema::hasColumn('disputes', 'evidence_photos')) {
                $table->dropColumn('evidence_photos');
            }

            if (Schema::hasColumn('disputes', 'rented_rental_id')) {
                $table->dropConstrainedForeignId('rented_rental_id');
            }
        });
    }
};