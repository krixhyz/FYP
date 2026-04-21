<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            if (!Schema::hasColumn('disputes', 'owner_claim_amount')) {
                $table->decimal('owner_claim_amount', 10, 2)->nullable()->after('favored_party');
            }

            if (!Schema::hasColumn('disputes', 'owner_award_amount')) {
                $table->decimal('owner_award_amount', 10, 2)->nullable()->after('owner_claim_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            if (Schema::hasColumn('disputes', 'owner_award_amount')) {
                $table->dropColumn('owner_award_amount');
            }

            if (Schema::hasColumn('disputes', 'owner_claim_amount')) {
                $table->dropColumn('owner_claim_amount');
            }
        });
    }
};
