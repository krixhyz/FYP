<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::query()
            ->with('category')
            ->where('status', 'available')
            ->where(function ($query) {
                if (Auth::check()) {
                    $query->where('approval_status', 'APPROVED');
                    return;
                }

                $query->whereIn('approval_status', ['APPROVED', 'PENDING']);
            })
            ->when(Auth::check(), fn ($query) => $query->where('user_id', '!=', Auth::id()))
            ->latest()
            ->take(6)
            ->get();

        $parentCategories = Category::query()
            ->whereNull('parent_id')
            ->with(['children:id,parent_id'])
            ->orderBy('name')
            ->get();

        $allCategoryIds = $parentCategories
            ->flatMap(fn ($category) => collect([$category->id])->merge($category->children->pluck('id')))
            ->unique()
            ->values();

        $visibleApprovalStatuses = Auth::check() ? ['APPROVED'] : ['APPROVED', 'PENDING'];

        $productCountByCategoryId = Product::query()
            ->selectRaw('category_id, COUNT(*) as total')
            ->where('status', 'available')
            ->whereIn('approval_status', $visibleApprovalStatuses)
            ->whereIn('category_id', $allCategoryIds)
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $topCategories = $parentCategories
            ->map(function ($category) use ($productCountByCategoryId) {
                $relatedIds = collect([$category->id])->merge($category->children->pluck('id'));

                $category->products_count = $relatedIds->sum(
                    fn ($id) => (int) ($productCountByCategoryId[(int) $id] ?? 0)
                );

                return $category;
            })
            ->sortByDesc('products_count')
            ->take(6)
            ->values();

        return view('landing', compact('featuredProducts', 'topCategories'));
    }
}
