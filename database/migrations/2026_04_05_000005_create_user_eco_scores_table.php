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
        Schema::create('user_eco_scores', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to users
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Transaction reference (polymorphic - could be order, rental, swap)
            $table->nullableMorphs('transaction'); // transaction_type, transaction_id
            
            // Eco impact details
            $table->decimal('eco_points_awarded', 10, 2);
            $table->string('product_category')->nullable();
            $table->string('condition')->nullable(); // NEW, LIKE_NEW, GOOD, FAIR, WORN_FOR_PARTS
            
            // Cumulative tracking at time of transaction
            $table->decimal('cumulative_eco_score', 12, 2)->default(0);
            $table->string('eco_level')->default('None'); // None, Bronze, Silver, Gold, Platinum
            
            // Metadata
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indices for fast lookups
            $table->index('user_id');
            $table->index('created_at');
            $table->index('eco_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_eco_scores');
    }
};
