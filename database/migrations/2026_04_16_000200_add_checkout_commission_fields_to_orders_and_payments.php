<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('total_price');
            }

            if (!Schema::hasColumn('orders', 'service_fee')) {
                $table->decimal('service_fee', 10, 2)->default(0)->after('subtotal');
            }

            if (!Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->default(0)->after('service_fee');
            }

            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('status');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'order_id')) {
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete()->after('user_id');
            }

            if (!Schema::hasColumn('payments', 'gross_amount')) {
                $table->decimal('gross_amount', 10, 2)->default(0)->after('amount');
            }

            if (!Schema::hasColumn('payments', 'fee_amount')) {
                $table->decimal('fee_amount', 10, 2)->default(0)->after('gross_amount');
            }

            if (!Schema::hasColumn('payments', 'seller_amount')) {
                $table->decimal('seller_amount', 10, 2)->default(0)->after('fee_amount');
            }

            if (!Schema::hasColumn('payments', 'platform_amount')) {
                $table->decimal('platform_amount', 10, 2)->default(0)->after('seller_amount');
            }

            if (!Schema::hasColumn('payments', 'fee_percentage')) {
                $table->decimal('fee_percentage', 5, 2)->default(0)->after('platform_amount');
            }

            if (!Schema::hasColumn('payments', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->unique()->after('transaction_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_reference')) {
                $table->dropUnique(['payment_reference']);
                $table->dropColumn('payment_reference');
            }

            if (Schema::hasColumn('payments', 'fee_percentage')) {
                $table->dropColumn('fee_percentage');
            }

            if (Schema::hasColumn('payments', 'platform_amount')) {
                $table->dropColumn('platform_amount');
            }

            if (Schema::hasColumn('payments', 'seller_amount')) {
                $table->dropColumn('seller_amount');
            }

            if (Schema::hasColumn('payments', 'fee_amount')) {
                $table->dropColumn('fee_amount');
            }

            if (Schema::hasColumn('payments', 'gross_amount')) {
                $table->dropColumn('gross_amount');
            }

            if (Schema::hasColumn('payments', 'order_id')) {
                $table->dropConstrainedForeignId('order_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }

            if (Schema::hasColumn('orders', 'total_amount')) {
                $table->dropColumn('total_amount');
            }

            if (Schema::hasColumn('orders', 'service_fee')) {
                $table->dropColumn('service_fee');
            }

            if (Schema::hasColumn('orders', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
        });
    }
};
