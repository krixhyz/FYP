<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'product_id',
        'payment_id',
        'transaction_type',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'reserved_until',
        'buyer_name',
        'buyer_phone',
        'buyer_email',
        'buyer_address',
    ];

    protected $casts = [
        'quantity' => 'integer', // NEW
        'reserved_until' => 'datetime',
    ];

    // Relationships
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
