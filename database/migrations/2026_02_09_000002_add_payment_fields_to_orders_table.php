<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_id')) {
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'reserved_until')) {
                $table->timestamp('reserved_until')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_id')) {
                $table->dropConstrainedForeignId('payment_id');
            }
            if (Schema::hasColumn('orders', 'reserved_until')) {
                $table->dropColumn('reserved_until');
            }
        });
    }
};
