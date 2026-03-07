<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            // Transaction reference — one filled, others null
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('rental_request_id')->nullable()->constrained('rental_requests')->onDelete('set null');
            $table->foreignId('swap_id')->nullable()->constrained('swaps')->onDelete('set null');
            $table->enum('transaction_type', ['order', 'rental', 'swap']);
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['open', 'in_review', 'resolved', 'dismissed'])->default('open');
            $table->text('admin_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
