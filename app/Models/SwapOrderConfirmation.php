<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SwapOrderConfirmation extends Model
{
    protected $fillable = [
        'swap_request_id',
        'owner_confirmed_at',
        'owner_notes',
        'requester_confirmed_at',
        'requester_notes',
        'final_completed_at',
        'auto_expired_at',
        'order_details_email_sent_at',
    ];

    protected $casts = [
        'owner_confirmed_at' => 'datetime',
        'requester_confirmed_at' => 'datetime',
        'final_completed_at' => 'datetime',
        'auto_expired_at' => 'datetime',
        'order_details_email_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the swap request this confirmation belongs to.
     */
    public function swapRequest(): BelongsTo
    {
        return $this->belongsTo(SwapRequest::class);
    }

    /**
     * Check if both parties have confirmed.
     */
    public function getBothConfirmedAttribute(): bool
    {
        return $this->owner_confirmed_at && $this->requester_confirmed_at;
    }

    /**
     * Check if confirmation is pending (awaiting at least one party).
     */
    public function isPending(): bool
    {
        return !$this->owner_confirmed_at || !$this->requester_confirmed_at;
    }

    /**
     * Check if confirmation has expired without mutual agreement.
     */
    public function hasExpired(): bool
    {
        return $this->auto_expired_at !== null;
    }

    /**
     * Check if confirmation is complete.
     */
    public function isComplete(): bool
    {
        return $this->final_completed_at !== null;
    }

    /**
     * Get confirmation status as human-readable string.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isComplete()) {
            return 'Completed';
        }

        if ($this->hasExpired()) {
            return 'Expired';
        }

        if ($this->both_confirmed) {
            return 'Both Confirmed';
        }

        if ($this->owner_confirmed_at) {
            return 'Owner Confirmed, Awaiting Requester';
        }

        if ($this->requester_confirmed_at) {
            return 'Requester Confirmed, Awaiting Owner';
        }

        return 'Awaiting Confirmations';
    }
}
