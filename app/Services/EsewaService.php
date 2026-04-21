<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EsewaService
{
    public function buildSignature(string $totalAmount, string $transactionUuid, string $productCode, string $secretKey): string
    {
        $message = 'total_amount=' . $totalAmount
            . ',transaction_uuid=' . $transactionUuid
            . ',product_code=' . $productCode;

        $hash = hash_hmac('sha256', $message, $secretKey, true);
        return base64_encode($hash);
    }

    public function buildSignedFields(): string
    {
        return 'total_amount,transaction_uuid,product_code';
    }

    public function verifySignature(array $payload, string $secretKey): bool
    {
        $signedFieldNames = $payload['signed_field_names'] ?? '';
        if ($signedFieldNames === '') {
            return false;
        }

        $fields = explode(',', $signedFieldNames);
        $pairs = [];
        foreach ($fields as $field) {
            $field = trim($field);
            if ($field === '' || !array_key_exists($field, $payload)) {
                return false;
            }
            $pairs[] = $field . '=' . $payload[$field];
        }

        $message = implode(',', $pairs);
        $hash = hash_hmac('sha256', $message, $secretKey, true);
        $expected = base64_encode($hash);

        return hash_equals($expected, (string) ($payload['signature'] ?? ''));
    }

    public function refundPayment(array $payload): array
    {
        $refundUrl = config('esewa.refund_url');

        if (blank($refundUrl)) {
            return [
                'ok' => false,
                'status' => 0,
                'body' => [],
                'message' => 'eSewa refund URL is not configured.',
            ];
        }

        $response = Http::acceptJson()->post($refundUrl, $payload);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json() ?? [],
        ];
    }
}
