<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'parent_id', 'base_co2_kg', 'reuse_pct', 'eco_points'];

    protected $casts = [
        'base_co2_kg' => 'decimal:2',
        'reuse_pct' => 'decimal:2',
        'eco_points' => 'decimal:2',
    ];

    /**
     * Parent category (for subcategories)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Child categories (for parent categories only)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Products in this category
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Check if this is a parent category
     */
    public function isParent(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if this is a subcategory
     */
    public function isSubcategory(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Get all parent categories
     */
    public static function getParents()
    {
        return self::whereNull('parent_id')->orderBy('name')->get();
    }

    /**
     * Get subcategories for a parent
     */
    public static function getSubcategories($parentId)
    {
        return self::where('parent_id', $parentId)->orderBy('name')->get();
    }
}
