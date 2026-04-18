<?php

namespace App\Http\Controllers\User;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\RentalRequest;
use App\Models\RentedRentals;
use App\Models\Swap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class DisputeController extends Controller
{
    /**
     * Show dispute form for a transaction.
     * GET /dispute/create?type=order&id=1
     */
    public function create(Request $request)
    {
        $type = $request->query('type');
        $id   = $request->query('id');

        $transaction = $this->resolveTransaction($type, $id);
        if (! $transaction) abort(404);

        if (! $this->isAuthorizedReporter($type, $transaction)) {
            abort(403);
        }

        $canSubmitOwnerClaim = $this->canSubmitOwnerClaim($type, $transaction);
        $maxOwnerClaim = $this->maxOwnerClaimAmount($type, $transaction);

        $existing = Dispute::where('reporter_id', Auth::id())
            ->where($this->txColumn($type), $id)
            ->first();

        return view('disputes.create', compact('type', 'id', 'transaction', 'existing', 'canSubmitOwnerClaim', 'maxOwnerClaim'));
    }

    /**
     * Store a new dispute.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type'        => 'required|in:order,rental,swap',
            'ref_id'      => 'required|integer',
            'subject'     => 'required|string|max:200',
            'description' => 'required|string|max:3000',
            'owner_claim_amount' => 'nullable|numeric|min:0',
            'evidence_photos' => 'nullable|array',
            'evidence_photos.*' => 'file|image|max:5120',
        ]);

        $type = $request->type;
        $id   = $request->ref_id;

        $transaction = $this->resolveTransaction($type, $id);
        if (! $transaction) abort(404);

        if (! $this->isAuthorizedReporter($type, $transaction)) {
            abort(403);
        }

        $canSubmitOwnerClaim = $this->canSubmitOwnerClaim($type, $transaction);
        $maxOwnerClaim = $this->maxOwnerClaimAmount($type, $transaction);
        $ownerClaimAmount = $canSubmitOwnerClaim
            ? min((float) ($request->owner_claim_amount ?? 0), $maxOwnerClaim)
            : null;

        if ($canSubmitOwnerClaim && (float) ($request->owner_claim_amount ?? 0) > $maxOwnerClaim) {
            return back()->withErrors([
                'owner_claim_amount' => 'Claim amount cannot exceed available deposit (Rs. ' . number_format($maxOwnerClaim, 2) . ').',
            ])->withInput();
        }

        $existing = Dispute::where('reporter_id', Auth::id())
            ->where($this->txColumn($type), $id)
            ->first();

        $existingPhotos = $existing?->evidence_photos ?? [];
        $newPhotos = $this->storeEvidencePhotos($request);
        $mergedPhotos = array_values(array_filter(array_merge($existingPhotos, $newPhotos)));

        Dispute::updateOrCreate(
            array_filter([
                'reporter_id'       => Auth::id(),
                $this->txColumn($type) => $id,
            ]),
            [
                'seller_id' => $this->resolveSellerId($type, $transaction),
                'transaction_type' => $type,
                'subject'          => $request->subject,
                'description'      => $request->description,
                'evidence_photos'   => $mergedPhotos,
                'owner_claim_amount' => $ownerClaimAmount,
                'owner_award_amount' => null,
                'status'           => 'open',
                'admin_notes'      => null,
            ]
        );

        return redirect()->route('products.myPurchases')->with('success', 'Dispute submitted. An admin will review it shortly.');
    }

    /**
     * User's own disputes list.
     */
    public function myDisputes()
    {
        $disputes = Dispute::where('reporter_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('disputes.my', compact('disputes'));
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function txColumn(string $type): string
    {
        return match($type) {
            'order'  => 'order_id',
            'rental' => 'rented_rental_id',
            'swap'   => 'swap_id',
        };
    }

    private function resolveTransaction(string $type, int $id): mixed
    {
        return match($type) {
            'order'  => Order::find($id),
            'rental' => RentedRentals::find($id) ?? RentalRequest::find($id),
            'swap'   => Swap::find($id),
            default  => null,
        };
    }

    private function isAuthorizedReporter(string $type, mixed $transaction): bool
    {
        $userId = (int) Auth::id();

        return match($type) {
            'order' => (int) ($transaction->buyer_id ?? 0) === $userId || (int) ($transaction->seller_id ?? 0) === $userId,
            'rental' => (int) ($transaction->renter_id ?? 0) === $userId || (int) ($transaction->owner_id ?? 0) === $userId,
            'swap' => (int) ($transaction->owner_a_id ?? 0) === $userId || (int) ($transaction->owner_b_id ?? 0) === $userId,
            default => false,
        };
    }

    private function canSubmitOwnerClaim(string $type, mixed $transaction): bool
    {
        if ($type !== 'rental' || !($transaction instanceof RentedRentals)) {
            return false;
        }

        return (int) $transaction->owner_id === (int) Auth::id();
    }

    private function maxOwnerClaimAmount(string $type, mixed $transaction): float
    {
        if ($type !== 'rental' || !($transaction instanceof RentedRentals)) {
            return 0.0;
        }

        $transaction->loadMissing('deposit');
        $deposit = (float) ($transaction->deposit?->amount ?? $transaction->rent_deposit ?? 0);

        return max($deposit, 0.0);
    }

    private function resolveSellerId(string $type, mixed $transaction): ?int
    {
        return match ($type) {
            'order' => (int) ($transaction->seller_id ?? 0) ?: null,
            'rental' => (int) ($transaction->owner_id ?? 0) ?: null,
            'swap' => (int) ($transaction->owner_b_id ?? 0) ?: null,
            default => null,
        };
    }

    private function storeEvidencePhotos(Request $request): array
    {
        if (!$request->hasFile('evidence_photos')) {
            return [];
        }

        $stored = [];

        foreach ($request->file('evidence_photos') as $file) {
            if (!$file) {
                continue;
            }

            $stored[] = $file->store('disputes/evidence', 'public');
        }

        return $stored;
    }
}
