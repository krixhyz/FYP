<?php

namespace App\Services;

use App\Models\PayoutRequest;
use App\Models\Wallet;
use App\Models\WalletLedgerEntry;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class WalletLedgerService
{
    public function getOrCreateUserWallet(int $userId): Wallet
    {
        return Wallet::firstOrCreate(
            ['wallet_type' => 'user', 'user_id' => $userId],
            [
                'currency' => 'NPR',
                'available_balance' => 0,
                'pending_payout_balance' => 0,
                'lifetime_credit' => 0,
                'lifetime_debit' => 0,
            ]
        );
    }

    public function getOrCreatePlatformWallet(): Wallet
    {
        return Wallet::firstOrCreate(
            ['wallet_type' => 'platform', 'user_id' => null],
            [
                'currency' => 'NPR',
                'available_balance' => 0,
                'pending_payout_balance' => 0,
                'lifetime_credit' => 0,
                'lifetime_debit' => 0,
            ]
        );
    }

    public function creditSaleIfMissing(
        int $userId,
        float $amount,
        string $entryType,
        string $referenceType,
        int $referenceId,
        ?array $metadata = null
    ): void {
        $amount = $this->toMoney($amount);
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($userId, $amount, $entryType, $referenceType, $referenceId, $metadata) {
            $wallet = Wallet::where('wallet_type', 'user')->where('user_id', $userId)->lockForUpdate()->first();
            if (!$wallet) {
                $wallet = $this->getOrCreateUserWallet($userId);
                $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            }

            $alreadyPosted = WalletLedgerEntry::where('wallet_id', $wallet->id)
                ->where('direction', 'credit')
                ->where('entry_type', $entryType)
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->exists();

            if ($alreadyPosted) {
                return;
            }

            $this->postCredit(
                $wallet,
                $amount,
                $entryType,
                $referenceType,
                $referenceId,
                'Seller credited from completed transaction',
                $metadata
            );
        });
    }

    public function creditPlatformFeeIfMissing(
        float $amount,
        string $entryType,
        string $referenceType,
        int $referenceId,
        ?array $metadata = null
    ): void {
        $amount = $this->toMoney($amount);
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($amount, $entryType, $referenceType, $referenceId, $metadata) {
            $wallet = Wallet::where('wallet_type', 'platform')->whereNull('user_id')->lockForUpdate()->first();
            if (!$wallet) {
                $wallet = $this->getOrCreatePlatformWallet();
                $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            }

            $alreadyPosted = WalletLedgerEntry::where('wallet_id', $wallet->id)
                ->where('direction', 'credit')
                ->where('entry_type', $entryType)
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->exists();

            if ($alreadyPosted) {
                return;
            }

            $this->postCredit(
                $wallet,
                $amount,
                $entryType,
                $referenceType,
                $referenceId,
                'Platform service fee credited',
                $metadata
            );
        });
    }

    public function requestPayout(User $user, float $amount, ?string $note = null): PayoutRequest
    {
        $amount = $this->toMoney($amount);

        return DB::transaction(function () use ($user, $amount, $note) {
            $wallet = Wallet::where('wallet_type', 'user')->where('user_id', $user->id)->lockForUpdate()->first();
            if (!$wallet) {
                $wallet = $this->getOrCreateUserWallet($user->id);
                $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            }

            if ((float) $wallet->available_balance < $amount) {
                throw new \RuntimeException('Insufficient wallet balance for payout request.');
            }

            $before = $this->toMoney((float) $wallet->available_balance);
            $after = $this->toMoney($before - $amount);

            $wallet->available_balance = $after;
            $wallet->pending_payout_balance = $this->toMoney((float) $wallet->pending_payout_balance + $amount);
            $wallet->lifetime_debit = $this->toMoney((float) $wallet->lifetime_debit + $amount);
            $wallet->save();

            $payout = PayoutRequest::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'status' => 'pending',
                'note' => $note,
                'requested_at' => now(),
            ]);

            WalletLedgerEntry::create([
                'wallet_id' => $wallet->id,
                'direction' => 'debit',
                'entry_type' => 'payout_hold',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => 'payout_request',
                'reference_id' => $payout->id,
                'description' => 'Payout request created and amount held.',
                'metadata' => [
                    'note' => $note,
                ],
                'created_by' => $user->id,
            ]);

            return $payout;
        });
    }

    public function approvePayout(PayoutRequest $payoutRequest, int $adminId, ?string $adminNote = null): PayoutRequest
    {
        return DB::transaction(function () use ($payoutRequest, $adminId, $adminNote) {
            $payout = PayoutRequest::lockForUpdate()->with('wallet')->findOrFail($payoutRequest->id);

            if ($payout->status !== 'pending') {
                throw new \RuntimeException('Only pending payout requests can be approved.');
            }

            $payout->status = 'approved';
            $payout->processed_by = $adminId;
            $payout->admin_note = $adminNote;
            $payout->approved_at = now();
            $payout->save();

            return $payout;
        });
    }

    public function rejectPayout(PayoutRequest $payoutRequest, int $adminId, string $reason): PayoutRequest
    {
        return DB::transaction(function () use ($payoutRequest, $adminId, $reason) {
            $payout = PayoutRequest::lockForUpdate()->with('wallet')->findOrFail($payoutRequest->id);
            $wallet = Wallet::lockForUpdate()->findOrFail($payout->wallet_id);

            if (!in_array($payout->status, ['pending', 'approved'], true)) {
                throw new \RuntimeException('Only pending or approved requests can be rejected.');
            }

            $amount = $this->toMoney((float) $payout->amount);

            $before = $this->toMoney((float) $wallet->available_balance);
            $after = $this->toMoney($before + $amount);

            $wallet->available_balance = $after;
            $wallet->pending_payout_balance = $this->toMoney(max(0, (float) $wallet->pending_payout_balance - $amount));
            $wallet->lifetime_debit = $this->toMoney(max(0, (float) $wallet->lifetime_debit - $amount));
            $wallet->save();

            $payout->status = 'rejected';
            $payout->processed_by = $adminId;
            $payout->rejection_reason = $reason;
            $payout->rejected_at = now();
            $payout->save();

            WalletLedgerEntry::create([
                'wallet_id' => $wallet->id,
                'direction' => 'credit',
                'entry_type' => 'payout_release',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => 'payout_request',
                'reference_id' => $payout->id,
                'description' => 'Payout request rejected and held amount released.',
                'metadata' => [
                    'reason' => $reason,
                ],
                'created_by' => $adminId,
            ]);

            return $payout;
        });
    }

    public function markPayoutPaid(PayoutRequest $payoutRequest, int $adminId, string $payoutReference, ?string $adminNote = null): PayoutRequest
    {
        return DB::transaction(function () use ($payoutRequest, $adminId, $payoutReference, $adminNote) {
            $payout = PayoutRequest::lockForUpdate()->with('wallet')->findOrFail($payoutRequest->id);
            $wallet = Wallet::lockForUpdate()->findOrFail($payout->wallet_id);

            if (!in_array($payout->status, ['pending', 'approved'], true)) {
                throw new \RuntimeException('Only pending or approved payout requests can be marked paid.');
            }

            $amount = $this->toMoney((float) $payout->amount);
            $wallet->pending_payout_balance = $this->toMoney(max(0, (float) $wallet->pending_payout_balance - $amount));
            $wallet->save();

            $payout->status = 'paid';
            $payout->processed_by = $adminId;
            $payout->admin_note = $adminNote;
            $payout->payout_reference = $payoutReference;
            $payout->approved_at = $payout->approved_at ?: now();
            $payout->paid_at = now();
            $payout->save();

            return $payout;
        });
    }

    private function postCredit(
        Wallet $wallet,
        float $amount,
        string $entryType,
        ?string $referenceType,
        ?int $referenceId,
        ?string $description,
        ?array $metadata
    ): void {
        $before = $this->toMoney((float) $wallet->available_balance);
        $after = $this->toMoney($before + $amount);

        $wallet->available_balance = $after;
        $wallet->lifetime_credit = $this->toMoney((float) $wallet->lifetime_credit + $amount);
        $wallet->save();

        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'direction' => 'credit',
            'entry_type' => $entryType,
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    private function toMoney(float $amount): float
    {
        return (float) number_format($amount, 2, '.', '');
    }
}
