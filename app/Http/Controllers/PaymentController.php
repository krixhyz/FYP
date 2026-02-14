<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Rental;
use App\Models\RentalRequest;
use App\Models\RentedRentals;
use App\Models\Swap;
use App\Models\SwapRequest;
use App\Services\EsewaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function createOrderPayment(Order $order, EsewaService $esewaService)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return redirect()->route('products.myPurchases')->with('info', 'Order already processed.');
        }

        if ($order->reserved_until && $order->reserved_until->isPast()) {
            $order->status = 'cancelled';
            $order->save();
            return redirect()->route('products.index')->with('error', 'Order reservation expired.');
        }

        $payment = $this->createPaymentForOrders([$order], $esewaService, 'order');

        return $this->renderEsewaForm($payment, $esewaService);
    }

    public function createCartPayment(EsewaService $esewaService)
    {
        $cartItems = Auth::user()->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty.');
        }

        try {
            $orders = DB::transaction(function () use ($cartItems) {
                $orders = [];
                $now = now();
                $reservationUntil = $now->copy()->addMinutes(config('esewa.reservation_minutes'));

                $cartItems = $cartItems->sortBy('product_id')->values();

                foreach ($cartItems as $item) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                    if (!$product || $product->quantity < 1) {
                        throw new \RuntimeException('No available stock for ' . ($product->title ?? 'product'));
                    }

                    $reservedQty = Order::where('product_id', $product->id)
                        ->where('status', 'pending')
                        ->where('reserved_until', '>', $now)
                        ->sum('quantity');

                    $availableQty = $product->quantity - $reservedQty;

                    if ($availableQty < $item->quantity) {
                        throw new \RuntimeException('Insufficient stock for ' . ($product->title ?? 'product'));
                    }

                    $unitPrice = $product->price ?? 0;
                    $totalPrice = $unitPrice * $item->quantity;

                    $orders[] = Order::create([
                        'buyer_id' => Auth::id(),
                        'product_id' => $product->id,
                        'transaction_type' => 'buy',
                        'quantity' => $item->quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'status' => 'pending',
                        'reserved_until' => $reservationUntil,
                    ]);
                }

                return $orders;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.checkout')->with('error', $e->getMessage());
        }

        $payment = $this->createPaymentForOrders($orders, $esewaService, 'cart');

        return $this->renderEsewaForm($payment, $esewaService);
    }

    public function esewaSuccess(Request $request, EsewaService $esewaService)
    {
        $payload = $this->decodeEsewaPayload($request);
        if (!$payload) {
            return redirect()->route('products.index')->with('error', 'Invalid payment response.');
        }

        $transactionUuid = $payload['transaction_uuid'] ?? null;
        if (!$transactionUuid) {
            return redirect()->route('products.index')->with('error', 'Missing transaction reference.');
        }

        $payment = Payment::where('transaction_uuid', $transactionUuid)->first();
        if (!$payment) {
            return redirect()->route('products.index')->with('error', 'Payment not found.');
        }

        $secretKey = config('esewa.secret_key');
        if (!$esewaService->verifySignature($payload, $secretKey)) {
            $payment->status = 'failed';
            $payment->response_payload = $payload;
            $payment->save();
            return redirect()->route('products.index')->with('error', 'Payment signature mismatch.');
        }

        $statusResponse = $this->checkEsewaStatus($payment);
        $payment->response_payload = [
            'callback' => $payload,
            'status' => $statusResponse,
        ];

        if (($statusResponse['status'] ?? '') !== 'COMPLETE') {
            $payment->status = 'pending';
            $payment->save();
            return redirect()->route('products.index')->with('info', 'Payment pending verification.');
        }

        $source = $payment->request_payload['source'] ?? 'order';

        if ($source === 'swap') {
            DB::transaction(function () use ($payment, $payload, $statusResponse) {
                $payment->status = 'complete';
                $payment->transaction_code = $payload['transaction_code'] ?? ($statusResponse['ref_id'] ?? null);
                $payment->save();

                $swapRequestId = $payment->request_payload['swap_request_id'] ?? null;
                $swapRequest = SwapRequest::with(['product', 'offeredProduct'])
                    ->lockForUpdate()
                    ->find($swapRequestId);

                if (!$swapRequest || $swapRequest->status !== 'awaiting_payment') {
                    return;
                }

                $swapRequest->status = 'accepted';
                $swapRequest->reserved_until = null;
                $swapRequest->save();

                if ($swapRequest->product && $swapRequest->product->quantity <= 0) {
                    $swapRequest->product->status = 'swapped';
                    $swapRequest->product->save();
                }

                if ($swapRequest->offeredProduct && $swapRequest->offeredProduct->quantity <= 0) {
                    $swapRequest->offeredProduct->status = 'swapped';
                    $swapRequest->offeredProduct->save();
                }

                Swap::create([
                    'swap_request_id' => $swapRequest->id,
                    'product_a_id' => $swapRequest->product_id,
                    'product_b_id' => $swapRequest->offered_product_id,
                    'owner_a_id' => $swapRequest->owner_id,
                    'owner_b_id' => $swapRequest->requester_id,
                    'offered_amount' => $swapRequest->offered_amount,
                    'notes' => $swapRequest->message,
                    'status' => 'completed',
                ]);
            });

            return redirect()->route('dashboard')->with('success', 'Swap payment completed successfully.');
        }

        if ($source === 'rental') {
            DB::transaction(function () use ($payment, $payload, $statusResponse) {
                $payment->status = 'complete';
                $payment->transaction_code = $payload['transaction_code'] ?? ($statusResponse['ref_id'] ?? null);
                $payment->save();

                $rentalRequestId = $payment->request_payload['rental_request_id'] ?? null;
                $rentalRequest = RentalRequest::with(['product', 'rental'])->lockForUpdate()->find($rentalRequestId);
                if (!$rentalRequest || $rentalRequest->status !== 'approved') {
                    return;
                }

                $rental = $rentalRequest->rental;
                if (!$rental) {
                    $rental = Rental::create([
                        'product_id' => $rentalRequest->product_id,
                        'owner_id' => $rentalRequest->owner_id,
                        'rent_fare' => $rentalRequest->product->rent_fare ?? 0,
                        'rent_deposit' => $rentalRequest->rent_deposit ?? 0,
                        'status' => 'rented',
                    ]);
                }

                RentedRentals::create([
                    'rental_id' => $rental->id,
                    'product_id' => $rentalRequest->product_id,
                    'owner_id' => $rentalRequest->owner_id,
                    'renter_id' => $rentalRequest->renter_id,
                    'rent_fare' => $rental->rent_fare ?? 0,
                    'rent_deposit' => $rentalRequest->rent_deposit ?? 0,
                    'rent_type' => $rental->rent_type ?? 'daily',
                    'duration' => $rentalRequest->duration,
                    'start_date' => $rentalRequest->start_date,
                    'end_date' => $rentalRequest->end_date,
                    'total_amount' => $rentalRequest->total_amount,
                    'payment_status' => 'paid',
                    'payment_reference' => $payment->transaction_code,
                    'status' => 'active',
                ]);

                if ($rentalRequest->product && $rentalRequest->product->quantity > 0) {
                    $rentalRequest->product->quantity -= 1;
                }
                if ($rentalRequest->product) {
                    $rentalRequest->product->status = $rentalRequest->product->quantity > 0 ? 'available' : 'rented';
                    $rentalRequest->product->save();
                }

                $rentalRequest->delete();
            });

            return redirect()->route('products.index')->with('success', 'Rental payment completed successfully.');
        }

        DB::transaction(function () use ($payment, $payload, $statusResponse) {
            $payment->status = 'complete';
            $payment->transaction_code = $payload['transaction_code'] ?? ($statusResponse['ref_id'] ?? null);
            $payment->save();

            $orders = $payment->orders()->with('product')->lockForUpdate()->get();

            foreach ($orders as $order) {
                if ($order->status === 'completed') {
                    continue;
                }

                $product = Product::where('id', $order->product_id)->lockForUpdate()->first();
                if ($product) {
                    $product->quantity -= $order->quantity;
                    if ($product->quantity <= 0) {
                        $product->quantity = 0;
                        $product->status = 'sold';
                    }
                    $product->save();
                }

                $order->status = 'completed';
                $order->save();
            }
        });

        if ($source === 'cart') {
            $productIds = $payment->orders()->pluck('product_id')->all();
            if (!empty($productIds)) {
                CartItem::where('user_id', $payment->user_id)
                    ->whereIn('product_id', $productIds)
                    ->delete();
            }
        }

        return redirect()->route('products.myPurchases')->with('success', 'Payment completed successfully.');
    }

    public function esewaFailure(Request $request)
    {
        $payload = $this->decodeEsewaPayload($request);
        $transactionUuid = $payload['transaction_uuid'] ?? null;

        if ($transactionUuid) {
            $payment = Payment::where('transaction_uuid', $transactionUuid)->first();
            if ($payment) {
                $payment->status = 'failed';
                $payment->response_payload = $payload;
                $payment->save();

                $source = $payment->request_payload['source'] ?? null;

                if ($source === 'swap') {
                    $swapRequestId = $payment->request_payload['swap_request_id'] ?? null;
                    if ($swapRequestId) {
                        $swapRequest = SwapRequest::find($swapRequestId);
                        if ($swapRequest && $swapRequest->status === 'awaiting_payment') {
                            $this->releaseSwapReservation($swapRequest);
                        }
                    }
                } else {
                    $payment->orders()->where('status', 'pending')->update([
                        'status' => 'cancelled',
                        'reserved_until' => now(),
                    ]);
                }
            }
        }

        return redirect()->route('products.index')->with('error', 'Payment failed or was cancelled.');
    }

    public function createRentalPayment(RentalRequest $rentalRequest, EsewaService $esewaService)
    {
        if ($rentalRequest->renter_id !== Auth::id()) {
            abort(403);
        }

        if ($rentalRequest->status !== 'approved') {
            return redirect()->route('products.index')->with('error', 'Rental request is not approved for payment.');
        }

        $totalAmount = (float) ($rentalRequest->total_amount ?? 0) + (float) ($rentalRequest->rent_deposit ?? 0);
        $totalAmount = $this->formatAmount($totalAmount);

        $transactionUuid = (string) Str::uuid();
        $productCode = config('esewa.product_code');

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'provider' => 'esewa',
            'transaction_uuid' => $transactionUuid,
            'product_code' => $productCode,
            'amount' => $totalAmount,
            'tax_amount' => 0,
            'service_charge' => 0,
            'delivery_charge' => 0,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'request_payload' => [
                'source' => 'rental',
                'rental_request_id' => $rentalRequest->id,
            ],
        ]);

        return $this->renderEsewaForm($payment, $esewaService);
    }

    public function createSwapPayment(SwapRequest $swapRequest, EsewaService $esewaService)
    {
        if ($swapRequest->requester_id !== Auth::id()) {
            abort(403);
        }

        if ($swapRequest->status !== 'awaiting_payment') {
            return redirect()->route('dashboard')->with('error', 'Swap is not awaiting payment.');
        }

        if (!$swapRequest->offered_product_id) {
            return redirect()->route('dashboard')->with('error', 'Swap requires an offered product to proceed.');
        }

        if ($swapRequest->reserved_until && $swapRequest->reserved_until->isPast()) {
            $this->releaseSwapReservation($swapRequest);
            return redirect()->route('dashboard')->with('error', 'Swap reservation expired.');
        }

        $totalAmount = (float) ($swapRequest->offered_amount ?? 0);
        if ($totalAmount <= 0) {
            return redirect()->route('swap.checkout', $swapRequest)->with('error', 'No payment required for this swap.');
        }

        $totalAmount = $this->formatAmount($totalAmount);
        $transactionUuid = (string) Str::uuid();
        $productCode = config('esewa.product_code');

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'provider' => 'esewa',
            'transaction_uuid' => $transactionUuid,
            'product_code' => $productCode,
            'amount' => $totalAmount,
            'tax_amount' => 0,
            'service_charge' => 0,
            'delivery_charge' => 0,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'request_payload' => [
                'source' => 'swap',
                'swap_request_id' => $swapRequest->id,
                'product_id' => $swapRequest->product_id,
                'offered_product_id' => $swapRequest->offered_product_id,
            ],
        ]);

        return $this->renderEsewaForm($payment, $esewaService);
    }

    private function createPaymentForOrders(array $orders, EsewaService $esewaService, string $source): Payment
    {
        $totalAmount = 0;
        foreach ($orders as $order) {
            $totalAmount += $order->total_price ?? 0;
        }

        $totalAmount = $this->formatAmount($totalAmount);
        $transactionUuid = (string) Str::uuid();
        $productCode = config('esewa.product_code');

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'provider' => 'esewa',
            'transaction_uuid' => $transactionUuid,
            'product_code' => $productCode,
            'amount' => $totalAmount,
            'tax_amount' => 0,
            'service_charge' => 0,
            'delivery_charge' => 0,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'request_payload' => [
                'orders' => collect($orders)->pluck('id')->all(),
                'source' => $source,
            ],
        ]);

        foreach ($orders as $order) {
            $order->payment_id = $payment->id;
            $order->save();
        }

        return $payment;
    }

    private function renderEsewaForm(Payment $payment, EsewaService $esewaService)
    {
        $productCode = $payment->product_code;
        $signedFieldNames = $esewaService->buildSignedFields();
        $totalAmount = $this->formatAmount($payment->total_amount);
        $transactionUuid = $payment->transaction_uuid;
        $secretKey = config('esewa.secret_key');

        $signature = $esewaService->buildSignature(
            $totalAmount,
            $transactionUuid,
            $productCode,
            $secretKey
        );

        $payload = [
            'amount' => $this->formatAmount($payment->amount),
            'tax_amount' => $this->formatAmount($payment->tax_amount),
            'product_service_charge' => $this->formatAmount($payment->service_charge),
            'product_delivery_charge' => $this->formatAmount($payment->delivery_charge),
            'total_amount' => $totalAmount,
            'transaction_uuid' => $transactionUuid,
            'product_code' => $productCode,
            'success_url' => config('esewa.success_url'),
            'failure_url' => config('esewa.failure_url'),
            'signed_field_names' => $signedFieldNames,
            'signature' => $signature,
        ];

        $payment->request_payload = array_merge($payment->request_payload ?? [], $payload);
        $payment->save();

        return view('payments.esewa_form', [
            'formUrl' => config('esewa.form_url'),
            'payload' => $payload,
        ]);
    }

    private function decodeEsewaPayload(Request $request): ?array
    {
        $encoded = $request->input('data') ?? $request->input('response') ?? $request->input('payload');

        if ($encoded) {
            $decoded = base64_decode($encoded, true);
            if ($decoded !== false) {
                $json = json_decode($decoded, true);
                if (is_array($json)) {
                    return $json;
                }
            }
        }

        $data = $request->all();
        return is_array($data) && !empty($data) ? $data : null;
    }

    private function checkEsewaStatus(Payment $payment): array
    {
        $url = config('esewa.status_url');
        $query = [
            'product_code' => $payment->product_code,
            'total_amount' => $this->formatAmount($payment->total_amount),
            'transaction_uuid' => $payment->transaction_uuid,
        ];

        $response = Http::get($url, $query);
        if (!$response->ok()) {
            return ['status' => 'AMBIGUOUS'];
        }

        return $response->json() ?? ['status' => 'AMBIGUOUS'];
    }

    private function releaseSwapReservation(SwapRequest $swapRequest): void
    {
        DB::transaction(function () use ($swapRequest) {
            $reqProduct = Product::lockForUpdate()->find($swapRequest->product_id);
            $offProduct = Product::lockForUpdate()->find($swapRequest->offered_product_id);

            if ($reqProduct) {
                $reqProduct->quantity += 1;
                $reqProduct->save();
            }

            if ($offProduct) {
                $offProduct->quantity += 1;
                $offProduct->save();
            }

            $swapRequest->status = 'cancelled';
            $swapRequest->reserved_until = null;
            $swapRequest->save();
        });
    }

    private function formatAmount($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
