<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->onDelete('cascade');
            $table->decimal('base_co2_kg', 10, 2);
            $table->decimal('reuse_pct', 5, 2);
            $table->decimal('eco_points', 10, 2);
            $table->timestamps();

            // Unique constraint on (name, parent_id) allows "Books" at different levels
            $table->unique(['name', 'parent_id']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
