<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('swap_order_confirmations', function (Blueprint $table) {
            if (!Schema::hasColumn('swap_order_confirmations', 'order_details_email_sent_at')) {
                $table->timestamp('order_details_email_sent_at')->nullable()->after('created_at')->comment('When order details email was sent to both parties');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('swap_order_confirmations', function (Blueprint $table) {
            if (Schema::hasColumn('swap_order_confirmations', 'order_details_email_sent_at')) {
                $table->dropColumn('order_details_email_sent_at');
            }
        });
    }
};
