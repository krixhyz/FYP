<?php


namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Dispute;
use App\Models\Review;
use App\Notifications\DisputeStatusUpdated;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function dashboard()
    {
        // Collections for the view
        $users = User::latest()->take(20)->get();
        $products = Product::with('user')->latest()->take(20)->get();

        // Stats
        $totalUsers = User::count();
        $totalProducts = Product::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $flaggedProducts = Product::where('flagged', true)->count();
        $openDisputes = Dispute::where('status', 'open')->count();
        $totalReviews = Review::count();

        return view('admin.dashboard', [
            'users'          => $users,
            'products'       => $products,
            'totalUsers'     => $totalUsers,
            'totalProducts'  => $totalProducts,
            'totalAdmins'    => $totalAdmins,
            'flaggedProducts'=> $flaggedProducts,
            'openDisputes'   => $openDisputes,
            'totalReviews'   => $totalReviews,
        ]);
    }

    public function users()
    {
        $users = User::paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function userShow($id)
    {
        $user = User::findOrFail($id);
        $products = $user->products;
        return view('admin.users.show', compact('user', 'products'));
    }

    public function userEdit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function userUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $id,
            'role' => 'in:user,admin',
        ]);

        $user->update($validated);
        return redirect()->route('admin.users')->with('success', 'User updated successfully'); // was admin.users.show
    }

    public function userDelete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully');
    }

    public function products()
    {
        $products = Product::with('user')->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function productShow($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    public function productFlag(Product $product)
    {
        // idempotent: only update when needed
        if (! $product->flagged) {
            $product->flagged = true;
            $product->save();
        }

        return redirect()->back()->with('success', 'Product flagged');
    }

    public function productUnflag(Product $product)
    {
        if ($product->flagged) {
            $product->flagged = false;
            $product->save();
        }

        return redirect()->back()->with('success', 'Product unflagged');
    }

    public function productDelete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('admin.products')->with('success', 'Product deleted successfully');
    }

    // ─── Disputes ─────────────────────────────────────────────────────────────

    public function disputes(Request $request)
    {
        $query = Dispute::with('reporter')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $disputes = $query->paginate(20)->withQueryString();

        return view('admin.disputes.index', compact('disputes'));
    }

    public function disputeShow(Dispute $dispute)
    {
        $dispute->load(['reporter', 'resolver', 'order.product', 'rentalRequest.product', 'swap']);
        return view('admin.disputes.show', compact('dispute'));
    }

    public function disputeResolve(Request $request, Dispute $dispute)
    {
        $request->validate([
            'status'      => 'required|in:in_review,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $dispute->status;

        $dispute->update([
            'status'      => $request->status,
            'admin_notes' => $request->admin_notes,
            'resolved_by' => in_array($request->status, ['resolved','dismissed']) ? auth()->id() : $dispute->resolved_by,
            'resolved_at' => in_array($request->status, ['resolved','dismissed']) ? now() : $dispute->resolved_at,
        ]);

        // Notify reporter only when status actually changed
        if ($oldStatus !== $request->status) {
            $dispute->reporter->notify(new DisputeStatusUpdated($dispute));
        }

        return redirect()->route('admin.disputes.show', $dispute)->with('success', 'Dispute updated.');
    }

    // ─── Reviews (admin read-only) ─────────────────────────────────────────────

    public function reviews()
    {
        $reviews = Review::with(['reviewer', 'reviewee'])->latest()->paginate(20);
        return view('admin.reviews.index', compact('reviews'));
    }
}