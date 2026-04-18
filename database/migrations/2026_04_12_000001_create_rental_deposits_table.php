<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rented_rental_id')->constrained('rented_rentals')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('deduction_amount', 10, 2)->default(0);
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->enum('status', ['held', 'refunded', 'partial', 'forfeited'])->default('held');
            $table->enum('refund_status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->string('gateway')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->string('refund_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('refund_requested_at')->nullable();
            $table->timestamp('refund_completed_at')->nullable();
            $table->timestamp('refund_failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->unique('rented_rental_id');
            $table->index(['status', 'refund_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_deposits');
    }
};