<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User\User;

class ProductPolicy
{
    public function modify(User $user, Product $product): bool
    {
        return (int) $product->user_id === (int) $user->id;
    }
}
