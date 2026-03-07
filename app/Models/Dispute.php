<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = [
        'reporter_id',
        'order_id',
        'rental_request_id',
        'swap_id',
        'transaction_type',
        'subject',
        'description',
        'status',
        'admin_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function rentalRequest()
    {
        return $this->belongsTo(RentalRequest::class);
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
