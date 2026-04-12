<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User\User;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'flagged',
        'price',
        'quantity',
        'type',
        'category_id',
        'condition',
        'image',
        'images',
        'status',
        'approval_status',
        'rent_duration',
    ];

    protected $casts = [
        'type' => 'array',
        'quantity' => 'integer',
        'flagged' => 'boolean',
        'images' => 'array',
        'approval_status' => 'string',
        'condition' => 'string',
    ];


    public function getTypeAttribute($value)
{
    // If it's already JSON or array, return as array
    if (is_array($value)) {
        return $value;
    }

    // If it's null or empty
    if (empty($value)) {
        return [];
    }

    // If it's a single string like "sell", make it an array
    if (is_string($value)) {
        // Try to decode JSON first
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Otherwise, split comma-separated strings
        return explode(',', $value);
    }

    // Fallback — return empty array
    return [];
}


public function owner()
{
    return $this->belongsTo(User::class, 'user_id');
}


public function rentals()
{
    return $this->hasOne(Rental::class);
}

    /** 
     * Get the user that owns the product.
     */
    public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}


public function receivedSwapRequests()
{
    return $this->hasMany(SwapRequest::class, 'product_id');
}

public function offeredSwapRequests()
{
    return $this->hasMany(SwapRequest::class, 'offered_product_id');
}

public function orders()
{
    return $this->hasMany(\App\Models\Order::class); // NEW
}

// Scopes for product filtering
public function scopeApproved($query)
{
    return $query->where('approval_status', 'APPROVED');
}

public function scopePending($query)
{
    return $query->where('approval_status', 'PENDING');
}

public function scopeRejected($query)
{
    return $query->where('approval_status', 'REJECTED');
}

// Helper methods
public function isApproved(): bool
{
    return $this->approval_status === 'APPROVED';
}

public function isPending(): bool
{
    return $this->approval_status === 'PENDING';
}

public function isRejected(): bool
{
    return $this->approval_status === 'REJECTED';
}

/**
 * Relationship: Product belongs to a Category
 */
public function category(): BelongsTo
{
    return $this->belongsTo(Category::class);
}
}
