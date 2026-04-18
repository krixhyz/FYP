<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupProductTempImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:cleanup-temp-images
                            {--hours=24 : Remove temporary images older than this many hours}
                            {--dry-run : Show files that would be deleted without deleting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete stale temporary product images from storage/uploads/tmp-products';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        if ($hours < 1) {
            $this->error('The --hours option must be at least 1.');
            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $directory = 'uploads/tmp-products';

        if (!$disk->exists($directory)) {
            $this->info('No temporary image directory found. Nothing to clean.');
            return self::SUCCESS;
        }

        $cutoffTimestamp = now()->subHours($hours)->timestamp;
        $dryRun = (bool) $this->option('dry-run');

        $files = $disk->allFiles($directory);
        $candidates = [];

        foreach ($files as $file) {
            try {
                $lastModified = $disk->lastModified($file);
            } catch (\Throwable $e) {
                $this->warn("Skipping {$file}: unable to read last-modified timestamp.");
                continue;
            }

            if ($lastModified <= $cutoffTimestamp) {
                $candidates[] = $file;
            }
        }

        if (empty($candidates)) {
            $this->info("No temporary images older than {$hours} hour(s) were found.");
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info('Dry run enabled. The following files would be deleted:');
            foreach ($candidates as $file) {
                $this->line("- {$file}");
            }

            $this->info('Total candidates: ' . count($candidates));
            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($candidates as $file) {
            if ($disk->delete($file)) {
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} stale temporary product image(s).");

        return self::SUCCESS;
    }
}
