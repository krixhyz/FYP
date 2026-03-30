<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Listing;

class PruneFlaggedListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listings:prune
                            {--days=30 : The number of days to keep flagged listings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete flagged listings that have been inactive for the specified number of days (default: 30 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the number of days to keep
        $days = $this->option('days');
        
        // Calculate the date threshold
        $cutoffDate = now()->subDays($days);

        // Delete all flagged listings older than the cutoff date
        $deletedCount = Listing::where('flagged', true)
            ->where('updated_at', '<', $cutoffDate)
            ->delete();

        $this->info("Deleted {$deletedCount} flagged listings that have been inactive for more than {$days} days.");
    }
}
