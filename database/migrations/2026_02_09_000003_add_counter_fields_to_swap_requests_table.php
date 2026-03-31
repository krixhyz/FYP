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
            if (!Schema::hasColumn('swap_requests', 'counter_amount')) {
                $table->decimal('counter_amount', 10, 2)->nullable()->after('offered_amount');
            }
            if (!Schema::hasColumn('swap_requests', 'counter_message')) {
                $table->text('counter_message')->nullable()->after('message');
            }
            if (!Schema::hasColumn('swap_requests', 'countered_at')) {
                $table->timestamp('countered_at')->nullable()->after('counter_message');
            }
        });

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE swap_requests MODIFY COLUMN status ENUM('requested','countered','accepted','rejected','cancelled') NOT NULL DEFAULT 'requested'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("UPDATE swap_requests SET status='requested' WHERE status='countered'");
            DB::statement("ALTER TABLE swap_requests MODIFY COLUMN status ENUM('requested','accepted','rejected','cancelled') NOT NULL DEFAULT 'requested'");
        }

        Schema::table('swap_requests', function (Blueprint $table) {
            if (Schema::hasColumn('swap_requests', 'counter_amount')) {
                $table->dropColumn('counter_amount');
            }
            if (Schema::hasColumn('swap_requests', 'counter_message')) {
                $table->dropColumn('counter_message');
            }
            if (Schema::hasColumn('swap_requests', 'countered_at')) {
                $table->dropColumn('countered_at');
            }
        });
    }
};
