<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Get all parent categories
     *
     * Endpoint: GET /api/categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($categories);
    }

    /**
     * Get subcategories for a parent
     *
     * Endpoint: GET /api/categories/{parentId}/subcategories
     */
    public function subcategories($parentId): JsonResponse
    {
        $subcategories = Category::where('parent_id', $parentId)
            ->orderBy('name')
            ->get(['id', 'name', 'base_co2_kg', 'reuse_pct', 'eco_points']);

        return response()->json($subcategories);
    }

    /**
     * Get single category with full details
     *
     * Endpoint: GET /api/categories/{id}
     */
    public function show($id): JsonResponse
    {
        $category = Category::findOrFail($id);

        return response()->json($category);
    }
}
