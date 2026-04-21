<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // Who wrote the review
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            // Who is being reviewed
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('cascade');
            // The transaction context (one of these will be non-null)
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('rented_rental_id')->nullable()->constrained('rented_rentals')->onDelete('cascade');
            $table->foreignId('swap_id')->nullable()->constrained('swaps')->onDelete('cascade');
            // Transaction type label for display
            $table->enum('transaction_type', ['order', 'rental', 'swap']);
            // Rating and text
            $table->tinyInteger('rating');      // 1-5
            $table->text('body')->nullable();
            $table->timestamps();

            // One review per reviewer per transaction
            $table->unique(['reviewer_id', 'order_id'], 'unique_review_order');
            $table->unique(['reviewer_id', 'rented_rental_id'], 'unique_review_rental');
            $table->unique(['reviewer_id', 'swap_id'], 'unique_review_swap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
