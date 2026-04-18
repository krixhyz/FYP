<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_type',
        'currency',
        'available_balance',
        'pending_payout_balance',
        'lifetime_credit',
        'lifetime_debit',
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'pending_payout_balance' => 'decimal:2',
        'lifetime_credit' => 'decimal:2',
        'lifetime_debit' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(WalletLedgerEntry::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class);
    }
}
