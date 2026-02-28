<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(private readonly LocationService $locationService)
    {
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:180'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $results = $this->locationService->search(
            $validated['q'],
            $validated['limit'] ?? 5
        );

        return response()->json($results);
    }

    public function reverse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $this->locationService->reverse(
            (float) $validated['lat'],
            (float) $validated['lng']
        );

        return response()->json($result ?? []);
    }
}
