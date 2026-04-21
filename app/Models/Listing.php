<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User\User;

class Listing extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'flagged',
    ];

    protected $casts = [
        'flagged' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}