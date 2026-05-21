<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Services\WalletLedgerService;
use Illuminate\Http\Request;

class WalletPayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = PayoutRequest::with(['user', 'wallet', 'processor'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $payoutRequests = $query->paginate(20)->withQueryString();

        return view('admin.wallet.payouts', compact('payoutRequests'));
    }

    public function approve(Request $request, PayoutRequest $payoutRequest, WalletLedgerService $walletService)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        try {
            $walletService->approvePayout($payoutRequest, auth()->id(), $validated['admin_note'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payout request approved.');
    }

    public function reject(Request $request, PayoutRequest $payoutRequest, WalletLedgerService $walletService)
    {
        if ($payoutRequest->status !== 'pending') {
            return back()->with('error', 'Only pending payout requests can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        try {
            $walletService->rejectPayout($payoutRequest, auth()->id(), $validated['rejection_reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payout request rejected and funds released.');
    }

    public function markPaid(Request $request, PayoutRequest $payoutRequest, WalletLedgerService $walletService)
    {
        $validated = $request->validate([
            'payout_reference' => 'required|string|max:255',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        try {
            $walletService->markPayoutPaid(
                $payoutRequest,
                auth()->id(),
                $validated['payout_reference'],
                $validated['admin_note'] ?? null
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payout request marked as paid.');
    }
}
