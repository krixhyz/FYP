<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'owner_id',
        'renter_id',
        'rent_fare',
        'rent_deposit',
        'rent_type',
        'duration',
        'start_date',
        'end_date',
        'total_amount',
        'payment_status',
        'payment_reference',
        'rental_status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }
}
