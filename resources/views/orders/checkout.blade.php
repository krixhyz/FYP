<x-app-layout>
    <div class="container mt-5">
        <div class="card shadow-lg p-4 mx-auto" style="max-width: 600px;">
            <h2 class="text-center mb-4">Order Checkout</h2>

            {{-- Case 1: Direct Purchase --}}
            @isset($order)
                <table class="table table-bordered">
                    <tr>
                        <th>Product</th>
                        <td>{{ $order->product->title }}</td>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <td>Rs. {{ $order->product->price }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{ ucfirst($order->status) }}</td>
                    </tr>
                    <tr>
                        <th>Transaction Type</th>
                        <td>{{ ucfirst($order->transaction_type) }}</td>
                    </tr>
                </table>

                <div class="alert alert-warning text-center mt-3">
                    <strong>Note:</strong> Payment integration coming soon. Your purchase is being processed.
                </div>

                <a href="{{ route('products.myPurchases') }}" class="btn btn-success w-100 mt-3">
                    Go to My Purchases
                </a>
            @endisset

            {{-- Case 2: Cart Checkout --}}
            @isset($cartItems)
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Price (Rs.)</th>
                            <th>Qty</th>
                            <th>Subtotal (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cartItems as $item)
                            <tr>
                                <td>{{ $item->product->title }}</td>
                                <td>{{ number_format($item->product->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->product->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">Total</td>
                            <td>Rs. {{ number_format($total, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="alert alert-warning text-center mt-3">
                    <strong>Note:</strong> Payment integration coming soon. Please confirm to place your order.
                </div>

                <form action="{{ route('orders.placeFromCart') }}" method="POST">
                    @csrf
                    <button class="btn btn-primary w-100 mt-3">Place Order</button>
                </form>
            @endisset
        </div>
    </div>
</x-app-layout>
