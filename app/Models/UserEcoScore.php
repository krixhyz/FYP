<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class UserEcoScore extends Model
{
    use HasFactory;

    protected $table = 'user_eco_scores';

    protected $fillable = [
        'user_id',
        'transaction_type',
        'transaction_id',
        'transaction_type_polymorphic',
        'eco_points_awarded',
        'product_category',
        'condition',
        'cumulative_eco_score',
        'eco_level',
        'notes',
    ];

    protected $casts = [
        'eco_points_awarded' => 'decimal:2',
        'cumulative_eco_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who earned this eco impact
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related transaction (Order, Rental, Swap, etc.)
     */
    public function transaction()
    {
        return $this->morphTo();
    }

    /**
     * Scope: Get eco scores for a user ordered by latest first
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)->latest('created_at');
    }

    /**
     * Scope: Get eco scores for a specific transaction type
     */
    public function scopeByTransactionType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope: Get eco scores from a specific date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Get users at a specific eco level
     */
    public function scopeByEcoLevel($query, $level)
    {
        return $query->where('eco_level', $level);
    }

    /**
     * Get all unique eco levels from the table
     */
    public static function getEcoLevels()
    {
        return ['None', 'Bronze', 'Silver', 'Gold', 'Platinum'];
    }

    /**
     * Determine eco level based on cumulative score
     */
    public static function calculateEcoLevel($cumulativeScore)
    {
        if ($cumulativeScore >= 5000) {
            return 'Platinum';
        } elseif ($cumulativeScore >= 3000) {
            return 'Gold';
        } elseif ($cumulativeScore >= 1500) {
            return 'Silver';
        } elseif ($cumulativeScore >= 500) {
            return 'Bronze';
        }
        return 'None';
    }

    /**
     * Get the threshold for each level
     */
    public static function getLevelThresholds()
    {
        return [
            'None' => 0,
            'Bronze' => 500,
            'Silver' => 1500,
            'Gold' => 3000,
            'Platinum' => 5000,
        ];
    }
}
