<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add category FK (nullable during migration)
            $table->foreignId('category_id')
                ->nullable()
                ->after('category')
                ->constrained('categories')
                ->onDelete('set null');

            // Add condition column
            $table->enum('condition', ['NEW', 'LIKE_NEW', 'GOOD', 'FAIR', 'WORN_FOR_PARTS'])
                ->default('GOOD')
                ->after('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeignKey(['category_id']);
            $table->dropColumn(['category_id', 'condition']);
        });
    }
};
