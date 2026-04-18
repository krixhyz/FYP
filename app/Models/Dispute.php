<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Dispute extends Model
{
    protected $fillable = [
        'reporter_id',
        'seller_id',
        'order_id',
        'rental_request_id',
        'rented_rental_id',
        'swap_id',
        'transaction_type',
        'subject',
        'description',
        'evidence_photos',
        'status',
        'favored_party',
        'owner_claim_amount',
        'owner_award_amount',
        'admin_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'evidence_photos' => 'array',
        'owner_claim_amount' => 'decimal:2',
        'owner_award_amount' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function rentalRequest()
    {
        return $this->belongsTo(RentalRequest::class);
    }

    public function rentedRental()
    {
        return $this->belongsTo(RentedRentals::class, 'rented_rental_id');
    }

    public function swap()
    {
        return $this->belongsTo(Swap::class);
    }

    public function statusBadge(): string
    {
        return match($this->status) {
            'open'      => 'bg-yellow-100 text-yellow-800',
            'in_review' => 'bg-blue-100 text-blue-800',
            'resolved'  => 'bg-green-100 text-green-800',
            'dismissed' => 'bg-gray-100 text-gray-600',
            default     => 'bg-gray-100 text-gray-600',
        };
    }
}
