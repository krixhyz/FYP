<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('wallet_type', ['user', 'platform'])->default('user');
            $table->string('currency', 8)->default('NPR');
            $table->decimal('available_balance', 14, 2)->default(0);
            $table->decimal('pending_payout_balance', 14, 2)->default(0);
            $table->decimal('lifetime_credit', 14, 2)->default(0);
            $table->decimal('lifetime_debit', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['wallet_type', 'user_id']);
            $table->unique(['wallet_type', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
