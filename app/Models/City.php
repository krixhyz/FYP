<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_id',
        'name',
        'slug',
        'is_active',
        'is_serviceable',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_serviceable' => 'boolean',
    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
