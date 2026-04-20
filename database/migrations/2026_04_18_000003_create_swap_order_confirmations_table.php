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
        Schema::create('swap_order_confirmations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('swap_request_id')->unique();
            $table->timestamp('owner_confirmed_at')->nullable()->comment('When owner confirmed receipt');
            $table->text('owner_notes')->nullable()->comment('Owner notes about receipt');
            $table->timestamp('requester_confirmed_at')->nullable()->comment('When requester confirmed receipt');
            $table->text('requester_notes')->nullable()->comment('Requester notes about receipt');
            $table->timestamp('final_completed_at')->nullable()->comment('When both confirmed and swap completed');
            $table->timestamp('auto_expired_at')->nullable()->comment('If confirmation takes too long, auto-expire');
            $table->timestamps();

            // Foreign key
            $table->foreign('swap_request_id')
                  ->references('id')
                  ->on('swap_requests')
                  ->onDelete('cascade');

            // Index
            $table->index('swap_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swap_order_confirmations');
    }
};
