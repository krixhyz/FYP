<?php

namespace App\Policies;

use App\Models\SwapRequest;
use App\Models\User\User;

class SwapRequestPolicy
{
    public function view(User $user, SwapRequest $swapRequest): bool
    {
        return (int) $swapRequest->owner_id === (int) $user->id
            || (int) $swapRequest->requester_id === (int) $user->id;
    }

    public function ownerManage(User $user, SwapRequest $swapRequest): bool
    {
        return (int) $swapRequest->owner_id === (int) $user->id;
    }

    public function requesterManage(User $user, SwapRequest $swapRequest): bool
    {
        return (int) $swapRequest->requester_id === (int) $user->id;
    }

    public function pay(User $user, SwapRequest $swapRequest): bool
    {
        $payerId = match ($swapRequest->money_direction) {
            'requester_offers_cash' => $swapRequest->requester_id,
            'owner_asks_cash' => $swapRequest->owner_id,
            default => null,
        };

        return $payerId !== null && (int) $payerId === (int) $user->id;
    }
}
