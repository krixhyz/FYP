<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rented_rentals', function (Blueprint $table) {
            // Notification flags to prevent duplicate alerts
            $table->boolean('notified_before')->default(false)->after('status')
                ->comment('True if renter was notified about expiry tomorrow');
            
            $table->boolean('notified_on_expiry')->default(false)->after('notified_before')
                ->comment('True if renter was notified after rental expired');
        });
    }

    public function down(): void
    {
        Schema::table('rented_rentals', function (Blueprint $table) {
            $table->dropColumn(['notified_before', 'notified_on_expiry']);
        });
    }
};
