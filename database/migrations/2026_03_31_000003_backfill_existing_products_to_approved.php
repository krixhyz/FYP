<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill all existing products to APPROVED status
        DB::table('products')->update(['approval_status' => 'APPROVED']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset to PENDING as default
        DB::table('products')->update(['approval_status' => 'PENDING']);
    }
};
