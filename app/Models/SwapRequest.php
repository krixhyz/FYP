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
        'asking_amount',
        'counter_amount',
        'message',
        'counter_message',
        'countered_at',
        'reserved_until',
        'order_details_sent_at',
        'money_direction',
        'payment_id',
        'status',
    ];

    protected $casts = [
        'countered_at' => 'datetime',
        'reserved_until' => 'datetime',
        'order_details_sent_at' => 'datetime',
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

    /**
     * Get all negotiation events for this swap request (immutable timeline).
     */
    public function negotiationEvents()
    {
        return $this->hasMany(SwapNegotiationEvent::class);
    }

    /**
     * Get the order confirmation tracking for this swap.
     */
    public function orderConfirmation()
    {
        return $this->hasOne(SwapOrderConfirmation::class);
    }

    /**
     * Get human-readable money flow description.
     */
    public function getMoneyFlowAttribute(): string
    {
        return match($this->money_direction) {
            'owner_asks_cash' => "Owner asks Rs. " . number_format($this->asking_amount ?? 0, 2),
            'requester_offers_cash' => "Requester offers Rs. " . number_format($this->offered_amount ?? 0, 2),
            default => 'No cash involved',
        };
    }
}
