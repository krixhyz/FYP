<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'seller_id')) {
                $table->foreignId('seller_id')->nullable()->after('buyer_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'buyer_name')) {
                $table->string('buyer_name')->nullable()->after('seller_id');
            }
            if (!Schema::hasColumn('orders', 'buyer_phone')) {
                $table->string('buyer_phone')->nullable()->after('buyer_name');
            }
            if (!Schema::hasColumn('orders', 'buyer_email')) {
                $table->string('buyer_email')->nullable()->after('buyer_phone');
            }
            if (!Schema::hasColumn('orders', 'buyer_address')) {
                $table->text('buyer_address')->nullable()->after('buyer_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'seller_id')) {
                $table->dropConstrainedForeignId('seller_id');
            }
            if (Schema::hasColumn('orders', 'buyer_name')) {
                $table->dropColumn('buyer_name');
            }
            if (Schema::hasColumn('orders', 'buyer_phone')) {
                $table->dropColumn('buyer_phone');
            }
            if (Schema::hasColumn('orders', 'buyer_email')) {
                $table->dropColumn('buyer_email');
            }
            if (Schema::hasColumn('orders', 'buyer_address')) {
                $table->dropColumn('buyer_address');
            }
        });
    }
};
