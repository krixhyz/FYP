<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Review extends Model
{
    protected $fillable = [
        'reviewer_id',
        'reviewee_id',
        'product_id',
        'order_id',
        'rented_rental_id',
        'swap_id',
        'transaction_type',
        'rating',
        'body',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function rentedRental()
    {
        return $this->belongsTo(RentedRentals::class, 'rented_rental_id');
    }

    public function swap()
    {
        return $this->belongsTo(Swap::class);
    }
}
