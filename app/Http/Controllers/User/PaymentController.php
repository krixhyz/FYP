<?php

namespace App\Http\Controllers\User;

use App\Models\User\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Rental;
use App\Models\RentalRequest;
use App\Models\RentedRentals;
use App\Models\Swap;
use App\Models\SwapRequest;
use App\Services\EsewaService;
use App\Services\KhaltiService;
use App\Services\InventoryReservationService;
use App\Services\EcoScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function createDirectOrderPayment(
        Request $request,
        Product $product,
        EsewaService $esewaService,
        KhaltiService $khaltiService,
        InventoryReservationService $inventory
    ) {
        $provider = $this->resolveProvider($request);

        if ($product->user_id === Auth::id()) {
            return redirect()->route('products.show', $product->id)->with('error', 'You cannot buy your own product.');
        }

        if (!in_array('sell', $product->type ?? [])) {
            return redirect()->route('products.show', $product->id)->with('error', 'This item is not available for purchase.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'buyer_name' => 'required|string|max:255',
            'buyer_phone' => 'nullable|string|regex:/^[0-9]{10}$/|size:10',
            'buyer_email' => 'required|email',
            'buyer_address' => 'nullable|string',
        ]);

        $quantity = (int) $validated['quantity'];

        try {
            DB::transaction(function () use ($product, $quantity, $inventory) {
                $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();
                $inventory->ensurePurchasableQuantity($lockedProduct, $quantity, now());
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('products.show', $product->id)->with('error', $e->getMessage());
        }

        $unitPrice = (float) ($product->price ?? 0);
        $items = [[
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
        ]];

        $payment = $this->createPaymentForOrderItems($items, 'order', $provider);

        // Add buyer details to payment payload
        $buyerDetails = [
            'buyer_name' => $validated['buyer_name'],
            'buyer_phone' => $validated['buyer_phone'],
            'buyer_email' => $validated['buyer_email'],
            'buyer_address' => $validated['buyer_address'],
        ];
        $payload = $payment->request_payload ?? [];
        $payload['buyer_details'] = $buyerDetails;
        $payment->request_payload = $payload;
        $payment->save();

        return $this->initiatePayment($payment, $provider, $esewaService, $khaltiService, 'Order Payment');
    }

    public function createOrderPayment(Request $request, Order $order, EsewaService $esewaService, KhaltiService $khaltiService)
    {
        $provider = $this->resolveProvider($request);

        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return redirect()->route('products.myPurchases')->with('info', 'Order already processed.');
        }

        if (!in_array('sell', $order->product->type ?? [])) {
            return redirect()->route('products.myPurchases')->with('error', 'This item is no longer available for purchase.');
        }

        if ($order->reserved_until && $order->reserved_until->isPast()) {
            $order->status = 'cancelled';
            $order->save();
            return redirect()->route('products.index')->with('error', 'Order reservation expired.');
        }

        $payment = $this->createPaymentForOrders([$order], 'order', $provider);

        return $this->initiatePayment($payment, $provider, $esewaService, $khaltiService, 'Order Payment');
    }

    public function createCartPayment(Request $request, EsewaService $esewaService, KhaltiService $khaltiService, InventoryReservationService $inventory)
    {
        // Validate buyer details
        $validated = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_phone' => 'nullable|string|regex:/^[0-9]{10}$/|size:10',
            'buyer_email' => 'required|email',
            'buyer_address' => 'nullable|string',
            'payment_gateway' => 'required|in:esewa,khalti',
        ]);

        // Log for debugging
        \Log::info('createCartPayment - Validated buyer details', [
            'buyer_name' => $validated['buyer_name'],
            'buyer_phone' => $validated['buyer_phone'],
            'buyer_email' => $validated['buyer_email'],
            'buyer_address' => substr($validated['buyer_address'] ?? '', 0, 100),
        ]);

        $provider = $this->resolveProvider($request);

        $cartItems = Auth::user()->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.checkout')->with('error', 'Cart is empty.');
        }

        try {
            $items = DB::transaction(function () use ($cartItems, $inventory) {
                $items = [];
                $now = now();

                $cartItems = $cartItems->sortBy('product_id')->values();

                foreach ($cartItems as $item) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                    if (!$product || $product->quantity < 1) {
                        throw new \RuntimeException('No available stock for ' . ($product->title ?? 'product'));
                    }

                    try {
                        $inventory->ensurePurchasableQuantity($product, (int) $item->quantity, $now);
                    } catch (\RuntimeException $e) {
                        throw new \RuntimeException(($product->title ?? 'Product') . ': ' . $e->getMessage());
                    }

                    $unitPrice = (float) ($product->price ?? 0);
                    $items[] = [
                        'product_id' => $product->id,
                        'quantity' => (int) $item->quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $unitPrice * (int) $item->quantity,
                    ];
                }

                return $items;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.checkout')->with('error', $e->getMessage());
        }

        $payment = $this->createPaymentForOrderItems($items, 'cart', $provider);

        // Add buyer details to payment payload
        $buyerDetails = [
            'buyer_name' => $validated['buyer_name'],
            'buyer_phone' => $validated['buyer_phone'],
            'buyer_email' => $validated['buyer_email'],
            'buyer_address' => $validated['buyer_address'],
        ];
        $payload = $payment->request_payload ?? [];
        $payload['buyer_details'] = $buyerDetails;
        $payment->request_payload = $payload;
        $payment->save();

        return $this->initiatePayment($payment, $provider, $esewaService, $khaltiService, 'Cart Checkout');
    }

    public function esewaSuccess(Request $request, EsewaService $esewaService, InventoryReservationService $inventory)
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

        if ($payment->provider !== 'esewa') {
            return redirect()->route('products.index')->with('error', 'Payment provider mismatch.');
        }

        if ($payment->status === 'complete') {
            return $this->alreadyProcessedRedirect($payment);
        }

        $secretKey = config('esewa.secret_key');
        if (!$esewaService->verifySignature($payload, $secretKey)) {
            $this->failPaymentBySource($payment, [
                'callback' => $payload,
                'reason' => 'signature_mismatch',
            ], $inventory);
            return redirect()->route('products.index')->with('error', 'Payment signature mismatch.');
        }

        $statusResponse = $this->checkEsewaStatus($payment);
        $responsePayload = [
            'callback' => $payload,
            'status' => $statusResponse,
        ];

        if (($statusResponse['status'] ?? '') !== 'COMPLETE') {
            $payment->status = 'pending';
            $payment->response_payload = $responsePayload;
            $payment->save();
            return redirect()->route('products.index')->with('info', 'Payment pending verification.');
        }

        return $this->completePaymentBySource(
            $payment,
            $payload['transaction_code'] ?? ($statusResponse['ref_id'] ?? null),
            $inventory,
            $responsePayload
        );
    }

    public function esewaFailure(Request $request, InventoryReservationService $inventory)
    {
        $payload = $this->decodeEsewaPayload($request);
        $transactionUuid = $payload['transaction_uuid'] ?? null;

        if ($transactionUuid) {
            $payment = Payment::where('transaction_uuid', $transactionUuid)->first();
            if ($payment) {
                $this->failPaymentBySource($payment, $payload, $inventory);
                return $this->redirectBySource($payment, 'error', 'eSewa payment failed or was cancelled.');
            }
        }

        return redirect()->route('products.index')->with('error', 'Payment failed or was cancelled.');
    }

    public function khaltiReturn(Request $request, KhaltiService $khaltiService, InventoryReservationService $inventory)
    {
        $transactionUuid = (string) ($request->input('purchase_order_id') ?? $request->input('transaction_uuid'));
        if ($transactionUuid === '') {
            return redirect()->route('products.index')->with('error', 'Missing purchase order identifier.');
        }

        $payment = Payment::where('transaction_uuid', $transactionUuid)->first();
        if (!$payment) {
            return redirect()->route('products.index')->with('error', 'Payment not found.');
        }

        if ($payment->provider !== 'khalti') {
            return redirect()->route('products.index')->with('error', 'Payment provider mismatch.');
        }

        if ($payment->status === 'complete') {
            return $this->alreadyProcessedRedirect($payment);
        }

        $callbackPayload = $request->all();
        $pidx = (string) ($request->input('pidx') ?? ($payment->request_payload['khalti']['pidx'] ?? ''));
        if ($pidx === '') {
            $this->failPaymentBySource($payment, [
                'callback' => $callbackPayload,
                'reason' => 'missing_pidx',
            ], $inventory);

            return $this->redirectBySource($payment, 'error', 'Missing Khalti payment identifier.');
        }

        $lookupResponse = $khaltiService->lookupPayment($pidx);
        $lookupBody = is_array($lookupResponse['body'] ?? null) ? $lookupResponse['body'] : [];

        $responsePayload = [
            'callback' => $callbackPayload,
            'lookup' => $lookupBody,
            'lookup_http_status' => $lookupResponse['status'] ?? null,
        ];

        $lookupStatus = strtolower((string) ($lookupBody['status'] ?? ''));
        if (($lookupResponse['ok'] ?? false) && $lookupStatus === 'completed') {
            $receivedAmountPaisa = (int) ($lookupBody['total_amount'] ?? 0);
            $expectedAmountPaisa = $khaltiService->toPaisa((float) $payment->total_amount);

            if ($receivedAmountPaisa !== $expectedAmountPaisa) {
                $responsePayload['reason'] = 'amount_mismatch';
                $responsePayload['expected_amount_paisa'] = $expectedAmountPaisa;
                $responsePayload['received_amount_paisa'] = $receivedAmountPaisa;
                $this->failPaymentBySource($payment, $responsePayload, $inventory);

                return $this->redirectBySource($payment, 'error', 'Khalti amount verification failed.');
            }

            return $this->completePaymentBySource(
                $payment,
                (string) ($lookupBody['transaction_id'] ?? $request->input('transaction_id') ?? ''),
                $inventory,
                $responsePayload
            );
        }

        if ($lookupStatus === 'pending' || $lookupStatus === 'initiated') {
            $payment->status = 'pending';
            $payment->response_payload = $responsePayload;
            $payment->save();

            return $this->redirectBySource($payment, 'info', 'Khalti payment is pending verification.');
        }

        $this->failPaymentBySource($payment, $responsePayload, $inventory);
        return $this->redirectBySource($payment, 'error', 'Khalti payment failed or was cancelled.');
    }

    public function createRentalPayment(Request $request, RentalRequest $rentalRequest, EsewaService $esewaService, KhaltiService $khaltiService, InventoryReservationService $inventory)
    {
        $provider = $this->resolveProvider($request);

        if ($rentalRequest->renter_id !== Auth::id()) {
            abort(403);
        }

        if ($rentalRequest->status !== 'approved') {
            return redirect()->route('products.index')->with('error', 'Rental request is not approved for payment.');
        }

        if ($rentalRequest->reserved_until && $rentalRequest->reserved_until->isPast()) {
            $inventory->releaseRentalReservation($rentalRequest);
            return redirect()->route('products.index')->with('error', 'Rental reservation expired. Please submit a new request.');
        }

        // Validate buyer details
        $validated = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_phone' => 'nullable|string|regex:/^[0-9]{10}$/|size:10',
            'buyer_email' => 'required|email',
            'buyer_address' => 'nullable|string',
        ]);

        $totalAmount = (float) ($rentalRequest->total_amount ?? 0) + (float) ($rentalRequest->rent_deposit ?? 0);
        $totalAmount = $this->formatAmount($totalAmount);

        $transactionUuid = (string) Str::uuid();
        $productCode = $provider === 'esewa' ? config('esewa.product_code') : 'KHALTI';

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'provider' => $provider,
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
                'gateway' => $provider,
                'rental_request_id' => $rentalRequest->id,
                'buyer_details' => [
                    'buyer_name' => $validated['buyer_name'],
                    'buyer_phone' => $validated['buyer_phone'],
                    'buyer_email' => $validated['buyer_email'],
                    'buyer_address' => $validated['buyer_address'],
                ],
            ],
        ]);

        return $this->initiatePayment($payment, $provider, $esewaService, $khaltiService, 'Rental Checkout');
    }

    public function createSwapPayment(Request $request, SwapRequest $swapRequest, EsewaService $esewaService, KhaltiService $khaltiService, InventoryReservationService $inventory)
    {
        $provider = $this->resolveProvider($request);

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
            $inventory->releaseSwapReservation($swapRequest);
            return redirect()->route('dashboard')->with('error', 'Swap reservation expired.');
        }

        // Validate buyer details
        $validated = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_phone' => 'nullable|string|regex:/^[0-9]{10}$/|size:10',
            'buyer_email' => 'required|email',
            'buyer_address' => 'nullable|string',
        ]);

        $totalAmount = (float) ($swapRequest->offered_amount ?? 0);
        if ($totalAmount <= 0) {
            return redirect()->route('swap.checkout', $swapRequest)->with('error', 'No payment required for this swap.');
        }

        $totalAmount = $this->formatAmount($totalAmount);
        $transactionUuid = (string) Str::uuid();
        $productCode = $provider === 'esewa' ? config('esewa.product_code') : 'KHALTI';

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'provider' => $provider,
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
                'gateway' => $provider,
                'swap_request_id' => $swapRequest->id,
                'product_id' => $swapRequest->product_id,
                'offered_product_id' => $swapRequest->offered_product_id,
                'buyer_details' => [
                    'buyer_name' => $validated['buyer_name'],
                    'buyer_phone' => $validated['buyer_phone'],
                    'buyer_email' => $validated['buyer_email'],
                    'buyer_address' => $validated['buyer_address'],
                ],
            ],
        ]);

        return $this->initiatePayment($payment, $provider, $esewaService, $khaltiService, 'Swap Checkout');
    }

    private function createPaymentForOrders(array $orders, string $source, string $provider): Payment
    {
        $totalAmount = 0;
        foreach ($orders as $order) {
            $totalAmount += $order->total_price ?? 0;
        }

        $totalAmount = $this->formatAmount($totalAmount);
        $transactionUuid = (string) Str::uuid();
        $productCode = $provider === 'esewa' ? config('esewa.product_code') : 'KHALTI';

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'provider' => $provider,
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
                'gateway' => $provider,
            ],
        ]);

        foreach ($orders as $order) {
            $order->payment_id = $payment->id;
            $order->save();
        }

        return $payment;
    }

    private function createPaymentForOrderItems(array $items, string $source, string $provider): Payment
    {
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += (float) ($item['total_price'] ?? 0);
        }

        $totalAmount = $this->formatAmount($totalAmount);
        $transactionUuid = (string) Str::uuid();
        $productCode = $provider === 'esewa' ? config('esewa.product_code') : 'KHALTI';

        return Payment::create([
            'user_id' => Auth::id(),
            'provider' => $provider,
            'transaction_uuid' => $transactionUuid,
            'product_code' => $productCode,
            'amount' => $totalAmount,
            'tax_amount' => 0,
            'service_charge' => 0,
            'delivery_charge' => 0,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'request_payload' => [
                'source' => $source,
                'gateway' => $provider,
                'order_items' => $items,
            ],
        ]);
    }

    private function initiatePayment(Payment $payment, string $provider, EsewaService $esewaService, KhaltiService $khaltiService, string $purchaseOrderName)
    {
        if ($provider === 'khalti') {
            return $this->renderKhaltiRedirect($payment, $khaltiService, $purchaseOrderName);
        }

        return $this->renderEsewaForm($payment, $esewaService);
    }

    private function renderKhaltiRedirect(Payment $payment, KhaltiService $khaltiService, string $purchaseOrderName)
    {
        $secretKey = config('khalti.secret_key');
        $initiateUrl = config('khalti.initiate_url');

        if (blank($secretKey) || blank($initiateUrl)) {
            return redirect()
                ->route('products.index')
                ->with('error', 'Khalti payment is not configured. Please set KHALTI_SECRET_KEY and KHALTI_INITIATE_URL.');
        }

        $user = Auth::user();
        $payload = [
            'return_url' => config('khalti.return_url'),
            'website_url' => config('khalti.website_url'),
            'amount' => $khaltiService->toPaisa((float) $payment->total_amount),
            'purchase_order_id' => $payment->transaction_uuid,
            'purchase_order_name' => $purchaseOrderName,
            'customer_info' => array_filter([
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
            ]),
        ];

        $response = $khaltiService->initiatePayment($payload);
        $responseBody = is_array($response['body'] ?? null) ? $response['body'] : [];
        $paymentUrl = $responseBody['payment_url'] ?? null;

        $payment->request_payload = array_merge($payment->request_payload ?? [], [
            'khalti_initiate_payload' => $payload,
            'khalti_initiate_response' => $responseBody,
        ]);

        if (!($response['ok'] ?? false) || blank($paymentUrl)) {
            $payment->status = 'failed';
            $payment->response_payload = [
                'initiate_response' => $responseBody,
                'http_status' => $response['status'] ?? null,
            ];
            $payment->save();

            return redirect()->route('products.index')->with('error', 'Unable to initiate Khalti payment. Please try again.');
        }

        $payment->request_payload = array_merge($payment->request_payload, [
            'khalti' => [
                'pidx' => $responseBody['pidx'] ?? null,
                'payment_url' => $paymentUrl,
                'expires_at' => $responseBody['expires_at'] ?? null,
            ],
        ]);
        $payment->save();

        return redirect()->away($paymentUrl);
    }

    private function renderEsewaForm(Payment $payment, EsewaService $esewaService)
    {
        $productCode = $payment->product_code;
        $signedFieldNames = $esewaService->buildSignedFields();
        $totalAmount = $this->formatAmount($payment->total_amount);
        $transactionUuid = $payment->transaction_uuid;
        $secretKey = config('esewa.secret_key');
        $formUrl = config('esewa.form_url');

        if (blank($productCode) || blank($secretKey) || blank($formUrl)) {
            return redirect()
                ->route('products.index')
                ->with('error', 'eSewa payment is not configured. Please set ESEWA_PRODUCT_CODE, ESEWA_SECRET_KEY and ESEWA_FORM_URL.');
        }

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
            'formUrl' => $formUrl,
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

    private function formatAmount($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function completePaymentBySource(
        Payment $payment,
        ?string $transactionCode,
        InventoryReservationService $inventory,
        array $responsePayload = []
    ) {
        $source = $payment->request_payload['source'] ?? 'order';
        $ecoScoreService = new EcoScoreService();

        if ($source === 'swap') {
            DB::transaction(function () use ($payment, $transactionCode, $responsePayload, $inventory, $ecoScoreService) {
                $payment->status = 'complete';
                $payment->transaction_code = $transactionCode;
                $payment->response_payload = $responsePayload;
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

                if ($swapRequest->product) {
                    $inventory->normalizeAvailableStatus($swapRequest->product);
                    if ($swapRequest->product->quantity <= 0) {
                        $swapRequest->product->status = 'swapped';
                    }
                    $swapRequest->product->save();
                    
                    // Record eco-impact for swapped product
                    $ecoScoreService->recordEcoImpact($swapRequest->product, 'swap', $payment->user_id);
                }

                if ($swapRequest->offeredProduct) {
                    $inventory->normalizeAvailableStatus($swapRequest->offeredProduct);
                    if ($swapRequest->offeredProduct->quantity <= 0) {
                        $swapRequest->offeredProduct->status = 'swapped';
                    }
                    $swapRequest->offeredProduct->save();
                    
                    // Record eco-impact for offered product
                    $ecoScoreService->recordEcoImpact($swapRequest->offeredProduct, 'swap', $payment->user_id);
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
            $rentalCompleted = false;

            DB::transaction(function () use ($payment, $transactionCode, $responsePayload, &$rentalCompleted, $inventory, $ecoScoreService) {
                $payment->status = 'complete';
                $payment->transaction_code = $transactionCode;
                $payment->response_payload = $responsePayload;
                $payment->save();

                $rentalRequestId = $payment->request_payload['rental_request_id'] ?? null;
                $rentalRequest = RentalRequest::with(['product', 'rental'])->lockForUpdate()->find($rentalRequestId);
                if (!$rentalRequest || $rentalRequest->status !== 'approved') {
                    return;
                }

                if ($rentalRequest->reserved_until && $rentalRequest->reserved_until->isPast()) {
                    $inventory->releaseRentalReservation($rentalRequest);
                    $payment->status = 'failed';
                    $payment->save();
                    return;
                }

                if (!$rentalRequest->stock_reserved) {
                    $product = Product::lockForUpdate()->find($rentalRequest->product_id);
                    if (!$product || $product->quantity < 1) {
                        $payment->status = 'failed';
                        $payment->save();
                        return;
                    }

                    $inventory->consumeProductQuantity($product, 1, 'rented');

                    $rentalRequest->stock_reserved = true;
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
                
                // Record eco-impact for rented product
                $product = Product::find($rentalRequest->product_id);
                if ($product) {
                    $ecoScoreService->recordEcoImpact($product, 'rent', $payment->user_id);
                }

                $rentalRequest->delete();
                $rentalCompleted = true;
            });

            if (!$rentalCompleted) {
                return redirect()->route('products.index')->with('error', 'Rental payment could not be completed. Reservation expired or stock unavailable.');
            }

            return redirect()->route('products.index')->with('success', 'Rental payment completed successfully.');
        }

        DB::transaction(function () use ($payment, $transactionCode, $responsePayload, $inventory, $source, $ecoScoreService) {
            $orderItems = $payment->request_payload['order_items'] ?? [];
            $buyerDetails = $payment->request_payload['buyer_details'] ?? [];
            $legacyOrders = $payment->orders()->with('product')->lockForUpdate()->get();

            if (!empty($orderItems)) {
                $orderedItems = collect($orderItems)->sortBy('product_id')->values();

                foreach ($orderedItems as $item) {
                    $product = Product::where('id', $item['product_id'] ?? 0)->lockForUpdate()->first();
                    if (!$product) {
                        throw new \RuntimeException('Product no longer exists.');
                    }

                    $quantity = (int) ($item['quantity'] ?? 0);
                    $inventory->ensurePurchasableQuantity($product, $quantity, now());
                    $inventory->consumeProductQuantity($product, $quantity, 'sold');

                    $order = Order::create([
                        'buyer_id' => $payment->user_id,
                        'seller_id' => $product->user_id,
                        'product_id' => $product->id,
                        'payment_id' => $payment->id,
                        'transaction_type' => 'buy',
                        'quantity' => $quantity,
                        'unit_price' => (float) ($item['unit_price'] ?? ($product->price ?? 0)),
                        'total_price' => (float) ($item['total_price'] ?? 0),
                        'status' => 'completed',
                        'reserved_until' => null,
                        'buyer_name' => $buyerDetails['buyer_name'] ?? '',
                        'buyer_phone' => $buyerDetails['buyer_phone'] ?? '',
                        'buyer_email' => $buyerDetails['buyer_email'] ?? '',
                        'buyer_address' => $buyerDetails['buyer_address'] ?? '',
                    ]);

                    // Send notifications to seller
                    $seller = $product->owner ?? $product->user;
                    if ($seller) {
                        $seller->notify(new \App\Notifications\User\OrderNotification($order));
                        Mail::to($seller->email)->send(new \App\Mail\OrderCreated($order));
                    }
                    
                    // Record eco-impact for sold product
                    $ecoScoreService->recordEcoImpact($product, 'sell', $payment->user_id);
                }
            } else {
                foreach ($legacyOrders as $order) {
                    if ($order->status === 'completed') {
                        continue;
                    }

                    $product = Product::where('id', $order->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $inventory->consumeProductQuantity($product, (int) $order->quantity, 'sold');
                        
                        // Record eco-impact for sold product
                        $ecoScoreService->recordEcoImpact($product, 'sell', $payment->user_id);
                    }

                    $order->status = 'completed';
                    $order->save();
                }
            }

            $payment->status = 'complete';
            $payment->transaction_code = $transactionCode;
            $payment->response_payload = $responsePayload;
            $payment->save();

            if ($source === 'cart') {
                $productIds = collect($orderItems)->pluck('product_id')->filter()->all();
                if (!empty($productIds)) {
                    CartItem::where('user_id', $payment->user_id)
                        ->whereIn('product_id', $productIds)
                        ->delete();
                }
            }
        });

        return redirect()->route('products.myPurchases')->with('success', 'Payment completed successfully.');
    }

    private function failPaymentBySource(Payment $payment, array $responsePayload, InventoryReservationService $inventory): void
    {
        if ($payment->status === 'complete') {
            return;
        }

        $payment->status = 'failed';
        $payment->response_payload = $responsePayload;
        $payment->save();

        $source = $payment->request_payload['source'] ?? null;

        if ($source === 'swap') {
            $swapRequestId = $payment->request_payload['swap_request_id'] ?? null;
            if ($swapRequestId) {
                $swapRequest = SwapRequest::find($swapRequestId);
                if ($swapRequest && $swapRequest->status === 'awaiting_payment') {
                    $inventory->releaseSwapReservation($swapRequest);
                }
            }
            return;
        }

        if ($source === 'rental') {
            $rentalRequestId = $payment->request_payload['rental_request_id'] ?? null;
            if ($rentalRequestId) {
                $rentalRequest = RentalRequest::find($rentalRequestId);
                if ($rentalRequest && $rentalRequest->status === 'approved') {
                    $inventory->releaseRentalReservation($rentalRequest);
                }
            }
            return;
        }

        if ($payment->orders()->exists()) {
            $payment->orders()->where('status', 'pending')->update([
                'status' => 'cancelled',
                'reserved_until' => now(),
            ]);
        }
    }

    private function alreadyProcessedRedirect(Payment $payment)
    {
        return $this->redirectBySource($payment, 'info', 'Payment already processed.');
    }

    private function resolveProvider(Request $request): string
    {
        $validated = $request->validate([
            'payment_gateway' => ['required', 'in:esewa,khalti'],
        ]);

        return strtolower((string) $validated['payment_gateway']);
    }

    private function redirectBySource(Payment $payment, string $level, string $message)
    {
        $source = $payment->request_payload['source'] ?? 'order';

        if ($source === 'swap') {
            return redirect()->route('dashboard')->with($level, $message);
        }

        if ($source === 'rental') {
            return redirect()->route('products.index')->with($level, $message);
        }

        return redirect()->route('products.myPurchases')->with($level, $message);
    }
}
