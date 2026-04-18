<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'rented_rental_id',
        'payment_id',
        'amount',
        'deduction_amount',
        'refund_amount',
        'status',
        'refund_status',
        'gateway',
        'gateway_reference',
        'refund_reference',
        'notes',
        'processed_by',
        'processed_at',
        'refund_requested_at',
        'refund_completed_at',
        'refund_failed_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'refund_requested_at' => 'datetime',
        'refund_completed_at' => 'datetime',
        'refund_failed_at' => 'datetime',
    ];

    public function rentedRental()
    {
        return $this->belongsTo(RentedRentals::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}