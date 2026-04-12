@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-8 md:px-16 py-12 space-y-8">
    <!-- Hero Section -->
    <section class="bg-[#f3f3f3] px-6 md:px-8 py-8 border-b border-[rgba(189,202,189,0.3)]">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Seller Dashboard</p>
        <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mb-1">Incoming Orders</h1>
        <p class="font-manrope text-base text-[#444746]">View orders placed by buyers with complete delivery details.</p>
    </section>

    @if(session('success'))
        <div class="bg-[#d4edda] border-2 border-[#c3e6cb] text-[#155724] px-4 py-3 font-manrope text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-12 text-center">
            <p class="font-manrope text-lg text-[#888888] mb-4">No incoming orders yet</p>
            <p class="font-manrope text-sm text-[#999999] mb-6">
                Orders will appear here when buyers purchase your products.
            </p>
            <a href="{{ route('products.index') }}" class="inline-block bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-xs uppercase tracking-wider hover:brightness-110">
                Browse Products
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-white shadow-[0_4px_12px_rgba(0,0,0,0.08)] hover:shadow-[0_8px_24px_rgba(0,0,0,0.12)] transition-shadow duration-200 border-l-4 border-[#006a38]">
                    <div class="p-6">
                        <!-- Top Row: Product, Date, Status -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b border-[#f0f0f0]">
                            <div>
                                <p class="font-space text-[10px] uppercase tracking-widest text-[#888888] mb-1">Product</p>
                                <p class="font-manrope font-semibold text-[#1a1c1c]">{{ $order->product->title ?? 'Product Unavailable' }}</p>
                                <p class="font-manrope text-sm text-[#888888] mt-1">Order #{{ $order->id }}</p>
                            </div>
                            <div>
                                <p class="font-space text-[10px] uppercase tracking-widest text-[#888888] mb-1">Order Date</p>
                                <p class="font-manrope font-semibold text-[#1a1c1c]">{{ $order->created_at->format('M d, Y') }}</p>
                                <p class="font-manrope text-sm text-[#888888] mt-1">{{ $order->created_at->format('h:i A') }}</p>
                            </div>
                            <div>
                                <p class="font-space text-[10px] uppercase tracking-widest text-[#888888] mb-1">Status</p>
                                <span class="inline-block bg-[#006a38] text-white px-3 py-1 font-space text-xs font-bold uppercase tracking-wider rounded">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Buyer Details, Quantity, and Amount -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Buyer Contact Information -->
                            <div>
                                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#006a38] mb-4">Buyer Details</p>
                                
                                @if($order->buyer_name)
                                    <div class="mb-4">
                                        <p class="font-space text-[10px] uppercase tracking-widest text-[#888888]">Full Name</p>
                                        <p class="font-manrope font-semibold text-[#1a1c1c]">{{ $order->buyer_name }}</p>
                                    </div>
                                @endif
                                
                                @if($order->buyer_phone)
                                    <div class="mb-4">
                                        <p class="font-space text-[10px] uppercase tracking-widest text-[#888888]">Phone</p>
                                        <a href="tel:{{ $order->buyer_phone }}" class="font-manrope font-semibold text-[#006a38] hover:underline">
                                            {{ $order->buyer_phone }}
                                        </a>
                                    </div>
                                @endif
                                
                                @if($order->buyer_email)
                                    <div class="mb-4">
                                        <p class="font-space text-[10px] uppercase tracking-widest text-[#888888]">Email</p>
                                        <a href="mailto:{{ $order->buyer_email }}" class="font-manrope font-semibold text-[#006a38] hover:underline">
                                            {{ $order->buyer_email }}
                                        </a>
                                    </div>
                                @endif
                                
                                @if($order->buyer_address)
                                    <div>
                                        <p class="font-space text-[10px] uppercase tracking-widest text-[#888888]">Delivery Address</p>
                                        <p class="font-manrope text-[#1a1c1c] mt-2 leading-relaxed bg-[#f9f9f9] p-3 rounded border border-[#f0f0f0]">
                                            {{ $order->buyer_address }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Order Summary -->
                            <div>
                                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#006a38] mb-4">Order Summary</p>
                                
                                <div class="space-y-3 bg-[#f9f9f9] p-4 rounded border border-[#f0f0f0]">
                                    <div class="flex justify-between">
                                        <span class="font-manrope text-sm text-[#888888]">Quantity</span>
                                        <span class="font-manrope font-semibold text-[#1a1c1c]">{{ $order->quantity }} unit(s)</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-manrope text-sm text-[#888888]">Unit Price</span>
                                        <span class="font-manrope font-semibold text-[#1a1c1c]">Rs. {{ number_format($order->unit_price, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between pt-3 border-t border-[#e0e0e0]">
                                        <span class="font-space font-bold text-sm text-[#1a1c1c]">Total Amount</span>
                                        <span class="font-space font-bold text-lg text-[#006a38]">Rs. {{ number_format($order->total_price, 2) }}</span>
                                    </div>
                                </div>

                                <!-- View Detail Button -->
                                <div class="mt-4">
                                    <a href="{{ route('orders.detail', $order->id) }}" class="block w-full text-center bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-4 py-3 font-space font-bold text-xs uppercase tracking-wider hover:brightness-110 rounded">
                                        View Full Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="flex justify-center mt-8">
                {{ $orders->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
