<?php

namespace App\Services;

class CheckoutPricingService
{
    public const BUYER_SERVICE_FEE_PERCENT = 3.0;

    public function calculatePurchase(float $subtotal): array
    {
        $subtotal = $this->roundMoney($subtotal);
        $serviceFee = $this->roundMoney($subtotal * (self::BUYER_SERVICE_FEE_PERCENT / 100));

        return [
            'flow' => 'purchase',
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'deposit' => 0.0,
            'total_amount' => $this->roundMoney($subtotal + $serviceFee),
            'seller_amount' => $subtotal,
            'platform_amount' => $serviceFee,
            'fee_percentage' => self::BUYER_SERVICE_FEE_PERCENT,
        ];
    }

    public function calculateRent(float $rentFee, float $deposit): array
    {
        $rentFee = $this->roundMoney($rentFee);
        $deposit = $this->roundMoney($deposit);
        $serviceFee = $this->roundMoney($rentFee * (self::BUYER_SERVICE_FEE_PERCENT / 100));

        return [
            'flow' => 'rent',
            'subtotal' => $rentFee,
            'service_fee' => $serviceFee,
            'deposit' => $deposit,
            'total_amount' => $this->roundMoney($rentFee + $deposit + $serviceFee),
            'seller_amount' => $rentFee,
            'platform_amount' => $serviceFee,
            'fee_percentage' => self::BUYER_SERVICE_FEE_PERCENT,
        ];
    }

    public function calculateSwap(float $cashTopup = 0.0): array
    {
        $cashTopup = $this->roundMoney($cashTopup);

        return [
            'flow' => 'swap',
            'subtotal' => $cashTopup,
            'service_fee' => 0.0,
            'deposit' => 0.0,
            'total_amount' => $cashTopup,
            'seller_amount' => $cashTopup,
            'platform_amount' => 0.0,
            'fee_percentage' => 0.0,
        ];
    }

    private function roundMoney(float $amount): float
    {
        return (float) number_format($amount, 2, '.', '');
    }
}
