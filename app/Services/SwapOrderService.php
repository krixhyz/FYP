<?php

namespace App\Services;

use App\Models\SwapRequest;
use App\Models\SwapNegotiationEvent;
use App\Models\SwapOrderConfirmation;
use App\Models\Swap;
use Illuminate\Support\Facades\DB;

class SwapOrderService
{
    public function __construct(
        private WalletLedgerService $walletService,
        private EcoScoreService $ecoScoreService
    ) {}

    /**
     * Create a negotiation event in the immutable timeline.
     */
    public function createNegotiationEvent(
        SwapRequest $swapRequest,
        int $actorId,
        string $eventType,
        array $data = []
    ): SwapNegotiationEvent {
        return SwapNegotiationEvent::create([
            'swap_request_id' => $swapRequest->id,
            'actor_id' => $actorId,
            'event_type' => $eventType,
            'offered_product_id' => $data['offered_product_id'] ?? null,
            'offered_amount' => $data['offered_amount'] ?? null,
            'asking_amount' => $data['asking_amount'] ?? null,
            'message' => $data['message'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Move swap to awaiting payment state and reserve inventory.
     */
    public function acceptSwapAndInitiatePayment(SwapRequest $swapRequest): void
    {
        DB::transaction(function () use ($swapRequest) {
            // Update status
            $swapRequest->status = 'awaiting_payment';
            $swapRequest->save();

            // Create negotiation event
            $this->createNegotiationEvent(
                $swapRequest,
                auth()->id(),
                'accept',
                ['message' => 'Swap accepted, awaiting payment']
            );
        });
    }

    /**
     * Complete swap after both parties confirm receipt.
     * This is called after confirmReceived() when both have confirmed.
     */
    public function completeSwapAfterConfirmation(SwapRequest $swapRequest): void
    {
        DB::transaction(function () use ($swapRequest) {
            $swapRequest->loadMissing(['product.category', 'offeredProduct.category']);

            // Release funds from escrow
            $this->walletService->releaseSwapFunds($swapRequest);

            // Update swap request status
            $swapRequest->status = 'completed';
            $swapRequest->save();

            // Update confirmation with final timestamp
            $swapRequest->orderConfirmation()->update([
                'final_completed_at' => now(),
            ]);

            // Create negotiation event
            $this->createNegotiationEvent(
                $swapRequest,
                (int) $swapRequest->owner_id,
                'accept',
                [
                    'message' => 'Swap completed, both parties confirmed',
                    'metadata' => ['event_origin' => 'system'],
                ]
            );

            // Award eco points to both participants for their exchanged item.
            if ($swapRequest->product) {
                $this->ecoScoreService->recordEcoImpact(
                    $swapRequest->product,
                    'swap',
                    (int) $swapRequest->owner_id,
                    (int) $swapRequest->id
                );
            }

            if ($swapRequest->offeredProduct) {
                $this->ecoScoreService->recordEcoImpact(
                    $swapRequest->offeredProduct,
                    'swap',
                    (int) $swapRequest->requester_id,
                    (int) $swapRequest->id
                );
            }
        });
    }

    /**
     * Expire a swap confirmation if both parties haven't confirmed within timeframe.
     */
    public function expireSwapConfirmation(SwapRequest $swapRequest, string $reason = 'Confirmation timeout'): void
    {
        DB::transaction(function () use ($swapRequest, $reason) {
            $confirmation = $swapRequest->orderConfirmation;

            if (!$confirmation || $confirmation->auto_expired_at) {
                return; // Already expired or no confirmation record
            }

            // Release funds (idempotent)
            $this->walletService->releaseSwapFunds($swapRequest);

            // Update status
            $swapRequest->status = 'expired';
            $swapRequest->save();

            // Mark as expired
            $confirmation->auto_expired_at = now();
            $confirmation->save();

            // Create negotiation event
            $this->createNegotiationEvent(
                $swapRequest,
                (int) $swapRequest->owner_id,
                'cancel',
                [
                    'message' => $reason,
                    'metadata' => ['event_origin' => 'system'],
                ]
            );
        });
    }

    /**
     * Get full negotiation timeline for a swap request.
     */
    public function getNegotiationTimeline(SwapRequest $swapRequest)
    {
        return $swapRequest->negotiationEvents()
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Check if swap is eligible for payment.
     */
    public function canInitiatePayment(SwapRequest $swapRequest): bool
    {
        return in_array($swapRequest->status, ['requested', 'countered']) 
            && $swapRequest->offered_product_id !== null;
    }

    /**
     * Check if user can confirm received items.
     */
    public function canConfirmReceipt(SwapRequest $swapRequest, int $userId): bool
    {
        // Swap must be in paid status
        if ($swapRequest->status !== 'paid') {
            return false;
        }

        // Order confirmation must exist
        if (!$swapRequest->orderConfirmation) {
            return false;
        }

        // User must be either owner or requester
        return $userId === $swapRequest->owner_id || $userId === $swapRequest->requester_id;
    }

    /**
     * Check if both parties have confirmed.
     */
    public function areBothConfirmed(SwapRequest $swapRequest): bool
    {
        $confirmation = $swapRequest->orderConfirmation;
        return $confirmation && $confirmation->both_confirmed;
    }
}
