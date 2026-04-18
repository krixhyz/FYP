<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class KhaltiService
{
    public function initiatePayment(array $payload): array
    {
        $response = Http::withHeaders($this->authHeaders())
            ->acceptJson()
            ->post(config('khalti.initiate_url'), $payload);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json() ?? [],
        ];
    }

    public function lookupPayment(string $pidx): array
    {
        $response = Http::withHeaders($this->authHeaders())
            ->acceptJson()
            ->post(config('khalti.lookup_url'), [
                'pidx' => $pidx,
            ]);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json() ?? [],
        ];
    }

    public function refundPayment(array $payload): array
    {
        $refundUrl = config('khalti.refund_url');

        if (blank($refundUrl)) {
            return [
                'ok' => false,
                'status' => 0,
                'body' => [],
                'message' => 'Khalti refund URL is not configured.',
            ];
        }

        $response = Http::withHeaders($this->authHeaders())
            ->acceptJson()
            ->post($refundUrl, $payload);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json() ?? [],
        ];
    }

    public function toPaisa(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Key ' . config('khalti.secret_key'),
            'Content-Type' => 'application/json',
        ];
    }
}
