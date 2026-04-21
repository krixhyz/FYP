<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Review;

class PruneOrphanedReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:prune
                            {--days=30 : The number of days to keep orphaned reviews}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete reviews with no valid associated transaction (after specified number of days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the number of days
        $days = $this->option('days');
        
        // Calculate the date threshold
        $cutoffDate = now()->subDays($days);

        // Delete orphaned reviews (all transaction references are null) older than cutoff
        $deletedCount = Review::where('order_id', null)
            ->where('rented_rental_id', null)
            ->where('swap_id', null)
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $this->info("Deleted {$deletedCount} orphaned reviews with no associated transactions.");
    }
}
