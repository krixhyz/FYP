<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class SwapRequest extends Model
{
    protected $fillable = [
        'product_id',
        'offered_product_id',
        'owner_id',
        'requester_id',
        'offered_amount',
        'counter_amount',
        'message',
        'counter_message',
        'countered_at',
        'reserved_until',
        'status',
    ];

    protected $casts = [
        'countered_at' => 'datetime',
        'reserved_until' => 'datetime',
    ];

    // relationships
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function requestedProduct()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function offeredProduct()
    {
        return $this->belongsTo(Product::class, 'offered_product_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
