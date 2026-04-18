<?php

use App\Models\RentalRequest;
use App\Models\SwapRequest;
use App\Services\InventoryReservationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inventory:cleanup-expired-reservations', function () {
    $now = now();
    $released = 0;
    $inventory = app(InventoryReservationService::class);

    SwapRequest::where('status', 'awaiting_payment')
        ->whereNotNull('reserved_until')
        ->where('reserved_until', '<=', $now)
        ->orderBy('id')
        ->chunkById(100, function ($swapRequests) use (&$released, $inventory) {
            foreach ($swapRequests as $swapRequest) {
                $inventory->releaseSwapReservation($swapRequest);
                $fresh = SwapRequest::find($swapRequest->id);
                if ($fresh && $fresh->status === 'cancelled') {
                    $released++;
                }
            }
        });

    RentalRequest::where('status', 'approved')
        ->whereNotNull('reserved_until')
        ->where('reserved_until', '<=', $now)
        ->orderBy('id')
        ->chunkById(100, function ($rentalRequests) use (&$released, $inventory) {
            foreach ($rentalRequests as $rentalRequest) {
                $inventory->releaseRentalReservation($rentalRequest);
                $fresh = RentalRequest::find($rentalRequest->id);
                if ($fresh && $fresh->status === 'cancelled') {
                    $released++;
                }
            }
        });

    $this->info("Released {$released} expired reservations.");
})->purpose('Release expired swap and rental stock reservations.');

Schedule::command('notifications:prune')->daily();
Schedule::command('disputes:prune')->daily();
Schedule::command('listings:prune')->daily();
Schedule::command('reviews:prune')->daily();
Schedule::command('rental:check-expiry')->dailyAt('01:00');
Schedule::command('products:cleanup-temp-images --hours=24')->dailyAt('02:00');

Schedule::command('inventory:cleanup-expired-reservations')->everyMinute();
