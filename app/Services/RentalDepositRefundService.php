<?php

namespace App\Services;

use App\Models\RentalDeposit;

class RentalDepositRefundService
{
    public function __construct(
        private readonly KhaltiService $khaltiService,
        private readonly EsewaService $esewaService,
    ) {
    }

    public function refund(RentalDeposit $rentalDeposit): array
    {
        $rentalDeposit->loadMissing('payment');

        $provider = $rentalDeposit->gateway ?: $rentalDeposit->payment?->provider;
        $refundAmount = (float) $rentalDeposit->refund_amount;

        if ($refundAmount <= 0) {
            return [
                'ok' => true,
                'status' => 'skipped',
                'body' => [],
                'message' => 'No refund is required for this deposit.',
            ];
        }

        if (blank($provider)) {
            return [
                'ok' => false,
                'status' => 0,
                'body' => [],
                'message' => 'Unable to determine payment provider for this refund.',
            ];
        }

        $payment = $rentalDeposit->payment;
        $basePayload = [
            'transaction_code' => $payment?->transaction_code,
            'transaction_uuid' => $payment?->transaction_uuid,
            'amount' => $refundAmount,
            'reference' => 'deposit:' . $rentalDeposit->id,
        ];

        return match ($provider) {
            'khalti' => $this->khaltiService->refundPayment(array_filter($basePayload, static fn ($value) => !is_null($value) && $value !== '')),
            'esewa' => $this->esewaService->refundPayment(array_filter($basePayload, static fn ($value) => !is_null($value) && $value !== '')),
            default => [
                'ok' => false,
                'status' => 0,
                'body' => [],
                'message' => 'Unsupported payment provider for refund.',
            ],
        };
    }
}