<x-app-layout>
    {{-- Optional header slot --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Checkout
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 space-y-3">
                    <div class="text-sm text-gray-600">Order #{{ $order->id }}</div>
                    <div class="text-sm text-gray-600">
                        Amount: NPR {{ number_format(((int)($amount ?? 0)) / 100, 2) }}
                    </div>

                    <button id="khaltiBtn"
                            class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                        Pay with Khalti
                    </button>

                    <div id="khaltiStatus" class="text-sm mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Khalti Widget --}}
    <script src="https://khalti.com/static/khalti-checkout.js"></script>

    <script>
        const amountPaisa = {{ (int) ($amount ?? 0) }};
        const orderId = {{ (int) $order->id }};

        const config = {
            publicKey: "{{ config('payments.khalti.public_key') }}",
            productIdentity: "ORDER-{{ $order->id }}",
            productName: "Order #{{ $order->id }}",
            productUrl: "{{ route('order.checkout', $order->id) }}",
            eventHandler: {
                onSuccess: function (payload) {
                    const statusEl = document.getElementById('khaltiStatus');
                    statusEl.innerText = 'Verifying payment...';

                    fetch("{{ route('payments.khalti.verify') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        },
                        body: JSON.stringify({
                            token: payload.token,
                            amount: amountPaisa,
                            order_id: orderId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.ok) {
                            statusEl.innerText = 'Payment successful ✅';
                            // Optional redirect:
                            // window.location.href = "{{ route('order.confirm', $order->id) }}";
                        } else {
                            statusEl.innerText = 'Verification failed ❌';
                            console.error(data.error);
                        }
                    })
                    .catch(err => {
                        statusEl.innerText = 'Network error ❌';
                        console.error(err);
                    });
                },
                onError: function (error) {
                    document.getElementById('khaltiStatus').innerText = 'Error: ' + JSON.stringify(error);
                },
                onClose: function () {}
            }
        };

        const checkout = new KhaltiCheckout(config);
        document.getElementById("khaltiBtn").onclick = function () {
            if (amountPaisa <= 0) {
                document.getElementById('khaltiStatus').innerText = 'Invalid amount ❌';
                return;
            }
            checkout.show({ amount: amountPaisa });
        }
    </script>
</x-app-layout>
