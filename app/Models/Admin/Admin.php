<?php


namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Admin extends Model
{
    protected $fillable = [
        'user_id',
        'permissions',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}