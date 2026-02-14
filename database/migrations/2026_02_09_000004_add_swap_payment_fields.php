<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swap_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('swap_requests', 'reserved_until')) {
                $table->timestamp('reserved_until')->nullable()->after('countered_at');
            }
        });

        DB::statement("ALTER TABLE swap_requests MODIFY COLUMN status ENUM('requested','countered','awaiting_payment','accepted','rejected','cancelled') NOT NULL DEFAULT 'requested'");
    }

    public function down(): void
    {
        DB::statement("UPDATE swap_requests SET status='requested' WHERE status='awaiting_payment'");
        DB::statement("ALTER TABLE swap_requests MODIFY COLUMN status ENUM('requested','countered','accepted','rejected','cancelled') NOT NULL DEFAULT 'requested'");

        Schema::table('swap_requests', function (Blueprint $table) {
            if (Schema::hasColumn('swap_requests', 'reserved_until')) {
                $table->dropColumn('reserved_until');
            }
        });
    }
};
