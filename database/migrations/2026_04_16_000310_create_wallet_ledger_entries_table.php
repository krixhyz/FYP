<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->enum('direction', ['credit', 'debit']);
            $table->string('entry_type', 64);
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_before', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('reference_type', 64)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['wallet_id', 'created_at'], 'wle_wallet_created_idx');
            $table->index(['reference_type', 'reference_id'], 'wle_ref_type_id_idx');
            $table->index(['entry_type', 'direction'], 'wle_type_direction_idx');
            $table->index(['wallet_id', 'entry_type', 'reference_type', 'reference_id'], 'wle_wallet_type_ref_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_ledger_entries');
    }
};
