<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return (int) $order->buyer_id === (int) $user->id
            || (int) $order->seller_id === (int) $user->id;
    }

    public function buyerAccess(User $user, Order $order): bool
    {
        return (int) $order->buyer_id === (int) $user->id;
    }

    public function sellerAccess(User $user, Order $order): bool
    {
        return (int) $order->seller_id === (int) $user->id;
    }
}
