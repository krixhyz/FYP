<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User\User;
use App\Models\UserEcoScore;

class EcoScoreService
{
    private const CONDITION_MULTIPLIERS = [
        'NEW'              => 1.00,
        'LIKE_NEW'         => 0.95,
        'GOOD'             => 0.85,
        'FAIR'             => 0.70,
        'WORN_FOR_PARTS'   => 0.50,
    ];

    private const TRANSACTION_MULTIPLIERS = [
        'swap' => 1.2,
        'sell' => 1.0,
        'rent' => 0.6,
    ];

    /**
     * Calculate eco-score for a product transaction
     *
     * Formula: final_eco_points = base_eco_points × condition_multiplier × transaction_multiplier
     *
     * @param Product $product
     * @param string $condition (NEW, LIKE_NEW, GOOD, FAIR, WORN_FOR_PARTS)
     * @param string $transactionType (swap, sell, rent)
     * @return float
     */
    public function calculateEcoScore(
        Product $product,
        string $condition,
        string $transactionType
    ): float {
        // Get base eco-points from category
        $baseEcoPoints = $product->category?->eco_points ?? 0;

        // Get condition multiplier (default to GOOD if invalid)
        $conditionMultiplier = self::CONDITION_MULTIPLIERS[$condition] ?? 0.85;

        // Get transaction multiplier (default to SELL if invalid / unknown type)
        $transactionMultiplier = self::TRANSACTION_MULTIPLIERS[$transactionType] ?? 1.0;

        // Calculate final eco-points
        $finalEcoPoints = $baseEcoPoints * $conditionMultiplier * $transactionMultiplier;

        return round($finalEcoPoints, 2);
    }

    /**
     * Get preview eco-score (without knowing transaction type yet)
     * Used on listing form for real-time preview
     *
     * @param Product $product
     * @param string $condition
     * @return float
     */
    public function getPreviewEcoScore(Product $product, string $condition): float
    {
        $baseEcoPoints = $product->category?->eco_points ?? 0;
        $conditionMultiplier = self::CONDITION_MULTIPLIERS[$condition] ?? 0.85;

        // Return just base × condition (no transaction type)
        return round($baseEcoPoints * $conditionMultiplier, 2);
    }

    /**
     * Record eco-score after transaction completes
     * Call this in PaymentController after payment is confirmed
     * 
     * Saves to user_eco_scores table and returns the awarded points
     *
     * @param Product $product
     * @param string $transactionType
     * @param int $userId
     * @param int|null $transactionId
     * @return float
     */
    public function recordEcoImpact(
        Product $product,
        string $transactionType,
        int $userId,
        ?int $transactionId = null
    ): float
    {
        // Get condition (should be set before transaction)
        $condition = $product->condition ?? 'GOOD';

        // Calculate eco-points
        $ecoPoints = $this->calculateEcoScore($product, $condition, $transactionType);

        // Get cumulative score for this user
        $cumulativeScore = UserEcoScore::where('user_id', $userId)->sum('eco_points_awarded') + $ecoPoints;
        $ecoLevel = UserEcoScore::calculateEcoLevel($cumulativeScore);

        $payload = [
            'user_id' => $userId,
            'transaction_type' => $transactionType,
            'transaction_id' => $transactionId,
            'eco_points_awarded' => $ecoPoints,
            'product_category' => $product->category?->name ?? 'Unknown',
            'condition' => $condition,
            'cumulative_eco_score' => $cumulativeScore,
            'eco_level' => $ecoLevel,
            'notes' => "{$product->title} ({$condition}) via {$transactionType}",
        ];

        // Keep writes idempotent when a concrete transaction id exists.
        if ($transactionId !== null) {
            UserEcoScore::updateOrCreate(
                [
                    'user_id' => $userId,
                    'transaction_type' => $transactionType,
                    'transaction_id' => $transactionId,
                ],
                $payload
            );
        } else {
            UserEcoScore::create($payload);
        }

        // Re-sync persisted totals on users table for fast read access.
        $finalTotal = (float) UserEcoScore::where('user_id', $userId)->sum('eco_points_awarded');
        $finalLevel = UserEcoScore::calculateEcoLevel($finalTotal);

        User::whereKey($userId)->update([
            'total_eco_score' => $finalTotal,
            'eco_level' => $finalLevel,
        ]);

        return $ecoPoints;
    }

    /**
     * Get condition multiplier for frontend display
     */
    public static function getConditionMultiplier(string $condition): float
    {
        return self::CONDITION_MULTIPLIERS[$condition] ?? 0.85;
    }

    /**
     * Get all available conditions
     */
    public static function getAllConditions(): array
    {
        return array_keys(self::CONDITION_MULTIPLIERS);
    }

    /**
     * Format eco-score for display (e.g., "125.50 points")
     */
    public static function formatEcoScore(float $points): string
    {
        return number_format($points, 2) . ' points';
    }
}
