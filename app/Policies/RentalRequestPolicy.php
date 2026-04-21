<?php

namespace App\Policies;

use App\Models\RentalRequest;
use App\Models\User\User;

class RentalRequestPolicy
{
    public function view(User $user, RentalRequest $rentalRequest): bool
    {
        return (int) $rentalRequest->owner_id === (int) $user->id
            || (int) $rentalRequest->renter_id === (int) $user->id;
    }

    public function pay(User $user, RentalRequest $rentalRequest): bool
    {
        return (int) $rentalRequest->renter_id === (int) $user->id;
    }

    public function ownerManage(User $user, RentalRequest $rentalRequest): bool
    {
        return (int) $rentalRequest->owner_id === (int) $user->id;
    }

    public function cancel(User $user, RentalRequest $rentalRequest): bool
    {
        return (int) $rentalRequest->renter_id === (int) $user->id;
    }
}
