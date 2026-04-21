<?php

namespace App\Console\Commands;

use App\Models\RentedRentals;
use App\Notifications\User\RentExpiringSoonNotification;
use App\Notifications\User\RentExpiredNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckRentExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rental:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for rental expirations and send notifications to renters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking rental expirations...');

        // Notify about expirations tomorrow
        $this->checkExpiringTomorrow();

        // Mark expired rentals as completed
        $this->checkExpiredRentals();

        $this->info('Rental expiry check completed.');
    }

    /**
     * Find rentals expiring tomorrow and notify renters
     */
    private function checkExpiringTomorrow(): void
    {
        $tomorrow = now()->addDay()->startOfDay();
        
        $rentals = RentedRentals::where('status', 'active')
            ->where('notified_before', false)
            ->whereDate('end_date', $tomorrow->toDateString())
            ->with(['renter', 'product'])
            ->get();

        if ($rentals->isEmpty()) {
            $this->line('ℹ No rentals expiring tomorrow.');
            return;
        }

        foreach ($rentals as $rental) {
            try {
                // Send notification to renter
                $rental->renter->notify(new RentExpiringSoonNotification($rental));

                // Mark as notified
                $rental->update(['notified_before' => true]);

                $this->line("✓ Notified renter {$rental->renter->name} about rental {$rental->id} expiring tomorrow.");
                Log::info("Rental expiring soon notification sent.", [
                    'rental_id' => $rental->id,
                    'renter_id' => $rental->renter_id,
                    'end_date' => $rental->end_date,
                ]);
            } catch (\Exception $e) {
                $this->error("✗ Failed to notify renter for rental {$rental->id}: {$e->getMessage()}");
                Log::error("Failed to send rental expiring soon notification.", [
                    'rental_id' => $rental->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->line("Processed " . $rentals->count() . " expiring-soon rentals.");
    }

    /**
     * Find expired rentals and mark as completed
     */
    private function checkExpiredRentals(): void
    {
        $now = now();
        
        $rentals = RentedRentals::where('status', 'active')
            ->where('notified_on_expiry', false)
            ->where('end_date', '<', $now)
            ->with(['renter', 'product'])
            ->get();

        if ($rentals->isEmpty()) {
            $this->line('ℹ No expired rentals.');
            return;
        }

        foreach ($rentals as $rental) {
            try {
                // Mark as completed
                $rental->update([
                    'status' => 'completed',
                    'notified_on_expiry' => true,
                ]);

                // Send notification to renter
                $rental->renter->notify(new RentExpiredNotification($rental));

                $this->line("✓ Marked rental {$rental->id} as expired and notified renter {$rental->renter->name}.");
                Log::info("Rental marked as expired and notification sent.", [
                    'rental_id' => $rental->id,
                    'renter_id' => $rental->renter_id,
                    'end_date' => $rental->end_date,
                ]);
            } catch (\Exception $e) {
                $this->error("✗ Failed to process expired rental {$rental->id}: {$e->getMessage()}");
                Log::error("Failed to process expired rental.", [
                    'rental_id' => $rental->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->line("Processed " . $rentals->count() . " expired rentals.");
    }
}
