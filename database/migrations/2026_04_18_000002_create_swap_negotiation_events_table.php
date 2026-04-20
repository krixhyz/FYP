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
        Schema::create('swap_negotiation_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('swap_request_id');
            $table->unsignedBigInteger('actor_id');
            $table->enum('event_type', ['initial_offer', 'counter_offer', 'accept', 'reject', 'cancel']);
            $table->unsignedBigInteger('offered_product_id')->nullable();
            $table->decimal('offered_amount', 10, 2)->nullable();
            $table->decimal('asking_amount', 10, 2)->nullable();
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Foreign keys
            $table->foreign('swap_request_id')
                  ->references('id')
                  ->on('swap_requests')
                  ->onDelete('cascade');
            $table->foreign('actor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->foreign('offered_product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('set null');

            // Indexes for query performance
            $table->index('swap_request_id');
            $table->index('actor_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swap_negotiation_events');
    }
};
