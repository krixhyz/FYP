<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'transaction_uuid',
        'product_code',
        'amount',
        'tax_amount',
        'service_charge',
        'delivery_charge',
        'total_amount',
        'status',
        'transaction_code',
        'request_payload',
        'response_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
