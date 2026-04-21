<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwapNegotiationEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'swap_request_id',
        'actor_id',
        'event_type',
        'offered_product_id',
        'offered_amount',
        'asking_amount',
        'message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the swap request this event belongs to.
     */
    public function swapRequest(): BelongsTo
    {
        return $this->belongsTo(SwapRequest::class);
    }

    /**
     * Get the user who initiated this event.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User\User::class, 'actor_id');
    }

    /**
     * Get the product involved in this event (if applicable).
     */
    public function offeredProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'offered_product_id');
    }

    /**
     * Get human-readable event type label.
     */
    public function getEventLabelAttribute(): string
    {
        return match($this->event_type) {
            'initial_offer' => 'Initial Offer',
            'counter_offer' => 'Counter Offer',
            'accept' => 'Accepted',
            'reject' => 'Rejected',
            'cancel' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->event_type)),
        };
    }
}
