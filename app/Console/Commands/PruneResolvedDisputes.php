<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dispute;

class PruneResolvedDisputes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disputes:prune
                            {--days=10 : The number of days to keep resolved disputes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete disputes that have been resolved longer than the specified number of days (default: 10 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the number of days to keep
        $days = $this->option('days');
        
        // Calculate the date threshold
        $cutoffDate = now()->subDays($days);

        // Delete all resolved disputes older than the cutoff date
        $deletedCount = Dispute::where('status', 'resolved')
            ->where('resolved_at', '<', $cutoffDate)
            ->delete();

        $this->info("Deleted {$deletedCount} disputes that were resolved more than {$days} days ago.");
    }
}
