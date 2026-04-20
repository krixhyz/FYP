<?php

namespace App\Http\Controllers\User;

use App\Models\Review;
use App\Models\Order;
use App\Models\RentedRentals;
use App\Models\Swap;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    /**
     * Show the review form for a given transaction.
     * route: GET /review/create?type=order&id=1
     */
    public function create(Request $request)
    {
        $type = $request->query('type');
        $id   = $request->query('id');

        [$transaction, $reviewee, $existingReview] = $this->resolveTransaction($type, $id);

        if (! $transaction) {
            abort(404, 'Transaction not found.');
        }

        if (! $this->canReviewTransaction($type, $transaction)) {
            abort(403, 'You are not allowed to review this transaction.');
        }

        if (! $reviewee) {
            abort(403, 'Unable to resolve a valid review target for this transaction.');
        }

        // Prevent self-review
        if ($reviewee && $reviewee->id === Auth::id()) {
            abort(403, 'You cannot review yourself.');
        }

        if ($existingReview) {
            return redirect()->route('products.myPurchases')
                ->with('info', 'You have already submitted a review for this transaction.');
        }

        return view('reviews.create', compact('type', 'id', 'transaction', 'reviewee', 'existingReview'));
    }

    /**
     * Store a new review.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type'   => 'required|in:order,rental,swap',
            'ref_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'body'   => 'nullable|string|max:1000',
        ]);

        $type  = $request->type;
        $id    = $request->ref_id;

        [$transaction, $reviewee, $existingReview] = $this->resolveTransaction($type, $id);

        if (! $transaction) abort(404);
        if (! $this->canReviewTransaction($type, $transaction)) {
            abort(403, 'You are not allowed to review this transaction.');
        }
        if (! $reviewee) {
            abort(403, 'Unable to resolve a valid review target for this transaction.');
        }
        if ($reviewee->id === Auth::id()) abort(403);
        if ($existingReview) {
            return redirect()->route('products.myPurchases')
                ->with('info', 'You have already submitted a review for this transaction.');
        }

        $data = [
            'reviewer_id'      => Auth::id(),
            'reviewee_id'      => $reviewee->id,
            'transaction_type' => $type,
            'rating'           => $request->rating,
            'body'             => $request->body,
        ];

        if ($type === 'order') {
            $data['order_id'] = $id;
        } elseif ($type === 'rental') {
            $data['rented_rental_id'] = $id;
        } else {
            $data['swap_id'] = $id;
        }

        Review::create($data);

        return redirect()->route('products.myPurchases')->with('success', 'Review submitted!');
    }

    /**
     * Show all reviews for a user (public profile view).
     */
    public function userReviews(User $user)
    {
        return redirect()->route('users.show', $user);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function resolveTransaction(string $type, int $id): array
    {
        switch ($type) {
            case 'order':
                $tx = Order::with('product.user', 'buyer', 'seller')->find($id);
                if (! $tx) return [null, null, null];
                // Reviewer is buyer → reviewee is seller
                $reviewee = $tx->seller ?? $tx->product?->user;
                $existing = Review::where('reviewer_id', Auth::id())
                    ->where('order_id', $id)->first();
                return [$tx, $reviewee, $existing];

            case 'rental':
                $tx = RentedRentals::with('owner', 'renter')->find($id);
                if (! $tx) return [null, null, null];
                // Determine who is the "other" party
                $reviewee = $tx->renter_id === Auth::id() ? $tx->owner : $tx->renter;
                $existing = Review::where('reviewer_id', Auth::id())
                    ->where('rented_rental_id', $id)->first();
                return [$tx, $reviewee, $existing];

            case 'swap':
                $tx = Swap::with('ownerA', 'ownerB')->find($id);
                if (! $tx) return [null, null, null];
                $reviewee = $tx->owner_a_id === Auth::id() ? $tx->ownerB : $tx->ownerA;
                $existing = Review::where('reviewer_id', Auth::id())
                    ->where('swap_id', $id)->first();
                return [$tx, $reviewee, $existing];
        }

        return [null, null, null];
    }

    private function canReviewTransaction(string $type, $transaction): bool
    {
        $userId = Auth::id();

        return match ($type) {
            'order' => (int) $transaction->buyer_id === (int) $userId
                && $transaction->status === 'completed'
                && $transaction->transaction_type === 'buy',
            'rental' => in_array((int) $userId, [(int) $transaction->renter_id, (int) $transaction->owner_id], true)
                && $transaction->status === 'completed',
            'swap' => in_array((int) $userId, [(int) $transaction->owner_a_id, (int) $transaction->owner_b_id], true)
                && $transaction->status === 'completed',
            default => false,
        };
    }
}
