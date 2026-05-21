<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $parents = Category::whereNull('parent_id')
            ->withCount('products')
            ->with(['children' => fn($q) => $q->withCount('products')->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|min:2|max:100',
            'parent_id'   => 'nullable|integer|exists:categories,id',
            'base_co2_kg' => 'required|numeric|min:0|max:99999',
            'reuse_pct'   => 'required|numeric|min:0|max:100',
            'eco_points'  => 'nullable|numeric|min:0',
        ], [
            'name.required'        => 'Category name is required.',
            'name.min'             => 'Name must be at least 2 characters.',
            'parent_id.exists'     => 'Selected parent category does not exist.',
            'base_co2_kg.required' => 'CO₂ value is required.',
            'reuse_pct.required'   => 'Reuse % is required.',
            'reuse_pct.max'        => 'Reuse % cannot exceed 100.',
        ]);

        // Subcategories must have a parent_id
        if (!empty($validated['parent_id'])) {
            $parent = Category::find($validated['parent_id']);
            if ($parent && $parent->parent_id !== null) {
                return back()->withErrors(['parent_id' => 'Cannot nest more than two levels deep.'])->withInput();
            }
        }

        // Auto-calculate eco_points if not provided
        $validated['eco_points'] = $validated['eco_points']
            ?? round(($validated['base_co2_kg'] * $validated['reuse_pct']) / 100, 2);

        Category::create($validated);

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|min:2|max:100',
            'base_co2_kg' => 'required|numeric|min:0|max:99999',
            'reuse_pct'   => 'required|numeric|min:0|max:100',
            'eco_points'  => 'nullable|numeric|min:0',
        ], [
            'name.required'        => 'Category name is required.',
            'name.min'             => 'Name must be at least 2 characters.',
            'base_co2_kg.required' => 'CO₂ value is required.',
            'reuse_pct.required'   => 'Reuse % is required.',
            'reuse_pct.max'        => 'Reuse % cannot exceed 100.',
        ]);

        $validated['eco_points'] = $validated['eco_points']
            ?? round(($validated['base_co2_kg'] * $validated['reuse_pct']) / 100, 2);

        $category->update($validated);

        return back()->with('success', '"' . $category->name . '" updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Block deletion if products are directly assigned
        $directProductCount = $category->products()->count();
        if ($directProductCount > 0) {
            return back()->with('error', 'Cannot delete "' . $category->name . '": ' . $directProductCount . ' product(s) are assigned to it. Reassign them first.');
        }

        // Block deletion of parent if it has subcategories with products
        if ($category->parent_id === null) {
            $childrenWithProducts = $category->children()
                ->whereHas('products')
                ->count();
            if ($childrenWithProducts > 0) {
                return back()->with('error', 'Cannot delete "' . $category->name . '": one or more subcategories have products assigned.');
            }
        }

        $name = $category->name;
        $category->delete(); // cascades to children via DB constraint

        return back()->with('success', '"' . $name . '" deleted successfully.');
    }
}
