<x-app-layout>
    <div class="max-w-4xl mx-auto py-12 px-6">
        <div class="bg-white text-black shadow-2xl rounded-3xl overflow-hidden border border-gray-100">
            
            {{-- Header --}}
            <div class="bg-gradient-to-r from-indigo-600 via-blue-500 to-sky-400 text-white py-5 text-center">
                <h2 class="text-3xl font-bold tracking-wide flex items-center justify-center gap-2">
                     <span>Order Checkout</span>
                </h2>
                <p class="text-sm opacity-90 mt-1">Review your order before final confirmation</p>
            </div>

            <div class="p-8">
                {{-- Case 1: Direct Purchase --}}
                @isset($order)
                    <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-4 font-semibold text-gray-800 w-1/3">Product</td>
                                    <td class="px-5 py-4 text-gray-900">{{ $order->product->title }}</td>
                                </tr>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-4 font-semibold text-gray-800">Price</td>
                                    <td class="px-5 py-4 text-green-700 font-semibold">
                                        Rs. {{ number_format($order->product->price, 2) }}
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-4 font-semibold text-gray-800">Status</td>
                                    <td class="px-5 py-4 capitalize">
                                        <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-4 font-semibold text-gray-800">Transaction Type</td>
                                    <td class="px-5 py-4 capitalize text-indigo-700 font-medium">
                                        {{ $order->transaction_type }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg text-yellow-800 text-sm text-center shadow-sm">
                        <strong>💡 Note:</strong> Payment integration coming soon. Your purchase is being processed.
                    </div>

                    <a href="{{ route('products.myPurchases') }}" 
                       class="mt-6 block text-center bg-gradient-to-r from-green-500 to-emerald-600 hover:opacity-90 text-white font-semibold py-3 rounded-xl transition-all duration-300 shadow-md">
                        Go to My Purchases
                    </a>
                @endisset

                {{-- Case 2: Cart Checkout --}}
                @isset($cartItems)
                    <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Product</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Price (Rs.)</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Qty</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Subtotal (Rs.)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach ($cartItems as $item)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-5 py-3 font-medium text-gray-900">{{ $item->product->title }}</td>
                                        <td class="px-5 py-3 text-right text-gray-800">{{ number_format($item->product->price, 2) }}</td>
                                        <td class="px-5 py-3 text-center text-gray-800">{{ $item->quantity }}</td>
                                        <td class="px-5 py-3 text-right text-blue-700 font-semibold">
                                            Rs. {{ number_format($item->product->price * $item->quantity, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-100 font-bold">
                                    <td colspan="3" class="px-5 py-4 text-right text-gray-900">Total</td>
                                    <td class="px-5 py-4 text-right text-green-700 text-lg">Rs. {{ number_format($total, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg text-yellow-800 text-sm text-center shadow-sm">
                        <strong>💡 Note:</strong> Payment integration coming soon. Please confirm to place your order.
                    </div>

                    <form action="{{ route('orders.placeFromCart') }}" method="POST" class="mt-6">
                        @csrf
                        <button class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg text-yellow-800 text-sm text-center shadow-sm">
                            Place Order
                        </button>
                    </form>
                @endisset
            </div>
        </div>
    </div>
</x-app-layout>
