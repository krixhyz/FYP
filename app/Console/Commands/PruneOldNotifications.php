<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class PruneOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:prune
                            {--days=10 : The number of days to keep notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notifications that are older than the specified number of days (default: 10 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the number of days to keep
        $days = $this->option('days');
        
        // Calculate the date threshold
        $cutoffDate = now()->subDays($days);

        // Delete all notifications older than the cutoff date
        $deletedCount = DatabaseNotification::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$deletedCount} notifications older than {$days} days.");
    }
}
