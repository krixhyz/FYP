<?php

use App\Models\User\User;
use App\Models\UserEcoScore;
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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'total_eco_score')) {
                $table->decimal('total_eco_score', 12, 2)->default(0)->after('profile_status');
            }

            if (!Schema::hasColumn('users', 'eco_level')) {
                $table->string('eco_level')->default('None')->after('total_eco_score');
            }
        });

        // Backfill for existing records from user_eco_scores.
        User::query()->select('id')->chunkById(200, function ($users): void {
            foreach ($users as $user) {
                $total = (float) UserEcoScore::where('user_id', $user->id)->sum('eco_points_awarded');

                User::whereKey($user->id)->update([
                    'total_eco_score' => $total,
                    'eco_level' => UserEcoScore::calculateEcoLevel($total),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'eco_level')) {
                $table->dropColumn('eco_level');
            }

            if (Schema::hasColumn('users', 'total_eco_score')) {
                $table->dropColumn('total_eco_score');
            }
        });
    }
};
