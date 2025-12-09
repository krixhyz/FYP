<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // integer amount in paisa
            if (!Schema::hasColumn('orders', 'amount')) {
                $table->unsignedBigInteger('amount')->default(0)->after('id');
            }
            // optional fields to track origin
            if (!Schema::hasColumn('orders', 'status')) {
                $table->string('status')->default('pending')->after('amount');
            }
            if (!Schema::hasColumn('orders', 'context')) {
                $table->string('context')->nullable()->after('status'); // cart|rental|swap
            }
            if (!Schema::hasColumn('orders', 'meta')) {
                $table->json('meta')->nullable()->after('context');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('orders', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('orders', 'context')) {
                $table->dropColumn('context');
            }
            if (Schema::hasColumn('orders', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};