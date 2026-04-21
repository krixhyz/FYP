<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    public function provinces(): JsonResponse
    {
        $provinces = Cache::rememberForever('nepal_provinces', function () {
            return Province::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);
        });

        return response()->json($provinces);
    }

    public function cities(int $provinceId): JsonResponse
    {
        $cacheKey = "nepal_cities_{$provinceId}";

        $cities = Cache::rememberForever($cacheKey, function () use ($provinceId) {
            return City::query()
                ->where('province_id', $provinceId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'province_id', 'name', 'slug', 'is_serviceable']);
        });

        $trackedKeys = Cache::get('nepal_cities_keys', []);
        if (!in_array($cacheKey, $trackedKeys, true)) {
            $trackedKeys[] = $cacheKey;
            Cache::forever('nepal_cities_keys', $trackedKeys);
        }

        return response()->json($cities);
    }

    public function clearCache(): JsonResponse
    {
        Cache::forget('nepal_provinces');

        $trackedKeys = Cache::get('nepal_cities_keys', []);
        foreach ($trackedKeys as $key) {
            Cache::forget($key);
        }
        Cache::forget('nepal_cities_keys');

        return response()->json([
            'message' => 'Location caches cleared successfully.',
        ]);
    }
}
