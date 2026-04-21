<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\WalletLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index(WalletLedgerService $walletService)
    {
        $user = Auth::user();
        $wallet = $walletService->getOrCreateUserWallet($user->id);
        $wallet->load([
            'ledgerEntries' => fn ($query) => $query->latest()->limit(30),
            'payoutRequests' => fn ($query) => $query->latest()->limit(20),
        ]);

        return view('wallet.index', compact('wallet'));
    }

    public function requestPayout(Request $request, WalletLedgerService $walletService)
    {
        $wallet = $walletService->getOrCreateUserWallet((int) Auth::id());
        $maxPayable = (float) $wallet->available_balance;

        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/',
                'min:0.01',
                'max:' . $maxPayable,
            ],
            'note' => 'nullable|string|max:2000',
        ], [
            'amount.regex' => 'Amount must be a valid positive number with up to 2 decimal places.',
            'amount.min' => 'Amount must be greater than zero.',
            'amount.max' => 'Amount cannot exceed your payable wallet balance.',
        ]);

        try {
            $walletService->requestPayout(
                Auth::user(),
                (float) $validated['amount'],
                $validated['note'] ?? null
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payout request submitted successfully.');
    }
}
