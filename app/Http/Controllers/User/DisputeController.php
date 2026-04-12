<?php

namespace App\Http\Controllers\User;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\RentalRequest;
use App\Models\Swap;
use App\Notifications\User\DisputeStatusUpdated;
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

        $existing = Dispute::where('reporter_id', Auth::id())
            ->where($this->txColumn($type), $id)
            ->first();

        return view('disputes.create', compact('type', 'id', 'transaction', 'existing'));
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
        ]);

        $type = $request->type;
        $id   = $request->ref_id;

        $transaction = $this->resolveTransaction($type, $id);
        if (! $transaction) abort(404);

        Dispute::updateOrCreate(
            array_filter([
                'reporter_id'       => Auth::id(),
                $this->txColumn($type) => $id,
            ]),
            [
                'transaction_type' => $type,
                'subject'          => $request->subject,
                'description'      => $request->description,
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
            'rental' => 'rental_request_id',
            'swap'   => 'swap_id',
        };
    }

    private function resolveTransaction(string $type, int $id): mixed
    {
        return match($type) {
            'order'  => Order::find($id),
            'rental' => RentalRequest::find($id),
            'swap'   => Swap::find($id),
            default  => null,
        };
    }
}
