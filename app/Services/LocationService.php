<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class LocationService
{
    public function search(string $query, int $limit = 5): array
    {
        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->withHeaders($this->headers())
                ->get(config('services.nominatim.search_url'), [
                    'q' => $query,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'limit' => max(1, min($limit, 10)),
                ]);
        } catch (Throwable $exception) {
            return [];
        }

        if (! $response->ok()) {
            return [];
        }

        $rows = $response->json();
        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->map(function (array $row): array {
                $address = $row['address'] ?? [];

                return [
                    'place_id' => (string) ($row['place_id'] ?? ''),
                    'location_text' => (string) ($row['display_name'] ?? ''),
                    'city' => $this->extractCity($address),
                    'latitude' => isset($row['lat']) ? (float) $row['lat'] : null,
                    'longitude' => isset($row['lon']) ? (float) $row['lon'] : null,
                ];
            })
            ->filter(fn (array $row) => $row['latitude'] !== null && $row['longitude'] !== null)
            ->values()
            ->all();
    }

    public function reverse(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->withHeaders($this->headers())
                ->get(config('services.nominatim.reverse_url'), [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                ]);
        } catch (Throwable $exception) {
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        $row = $response->json();
        if (! is_array($row)) {
            return null;
        }

        $address = $row['address'] ?? [];

        return [
            'place_id' => (string) ($row['place_id'] ?? ''),
            'location_text' => (string) ($row['display_name'] ?? ''),
            'city' => $this->extractCity($address),
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    private function extractCity(array $address): string
    {
        foreach (['city', 'town', 'village', 'municipality', 'county', 'state_district', 'state'] as $key) {
            if (! empty($address[$key])) {
                return (string) $address[$key];
            }
        }

        return '';
    }

    private function headers(): array
    {
        return [
            'User-Agent' => config('services.nominatim.user_agent'),
            'Accept-Language' => config('services.nominatim.language', 'en'),
        ];
    }
}
