<?php

namespace App\Policies;

use App\Models\RentedRentals;
use App\Models\User\User;

class RentedRentalPolicy
{
    public function view(User $user, RentedRentals $rentedRental): bool
    {
        return (int) $rentedRental->owner_id === (int) $user->id
            || (int) $rentedRental->renter_id === (int) $user->id;
    }

    public function requestReturn(User $user, RentedRentals $rentedRental): bool
    {
        return (int) $rentedRental->renter_id === (int) $user->id;
    }

    public function markReturned(User $user, RentedRentals $rentedRental): bool
    {
        return (int) $rentedRental->owner_id === (int) $user->id;
    }
}
