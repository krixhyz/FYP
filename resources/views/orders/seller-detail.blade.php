@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-8 md:px-16 py-12 space-y-8">
    <!-- Hero Section -->
    <section class="bg-[#f3f3f3] px-6 md:px-8 py-8 border-b border-[rgba(189,202,189,0.3)]">
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Order Details</p>
        <h1 class="font-space font-bold text-3xl text-[#1a1c1c] mb-1">Order #{{ $order->id }}</h1>
        <p class="font-manrope text-base text-[#444746]">Complete buyer information and payment details.</p>
    </section>

    <!-- Back Button -->
    <div>
        <a href="{{ route('orders.incoming') }}" class="inline-flex items-center gap-2 text-[#006a38] font-manrope font-semibold hover:underline">
            ← Back to Orders
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Information -->
            <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-8 border-l-4 border-[#006a38]">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#006a38] mb-6">Product Information</p>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <p class="font-space text-[10px] uppercase tracking-widest text-[#888888] mb-2">Product Name</p>
                        <p class="font-manrope font-bold text-2xl text-[#1a1c1c]">{{ $order->product->title ?? 'Product Unavailable' }}</p>
                    </div>

                    @if($order->product)
                        <div class="grid grid-cols-3 gap-4 bg-[#f9f9f9] p-4 rounded">
                            <div>
                                <p class="font-space text-[10px] uppercase tracking-widest text-[#888888] mb-2">Quantity</p>
                                <p class="font-manrope font-bold text-lg text-[#1a1c1c]">{{ $order->quantity }}</p>
                            </div>
                            <div>
                                <p class="font-space text-[10px] uppercase tracking-widest text-[#888888] mb-2">Unit Price</p>
                                <p class="font-manrope font-bold text-lg text-[#1a1c1c]">Rs. {{ number_format($order->unit_price, 2) }}</p>
                            </div>
                            <div>
                                <p class="font-space text-[10px] uppercase tracking-widest text-[#888888] mb-2">Total</p>
                                <p class="font-manrope font-bold text-lg text-[#006a38]">Rs. {{ number_format($order->total_price, 2) }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Buyer Contact Information -->
            <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-8 border-l-4 border-[#009a6d]">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#006a38] mb-6">Buyer Contact Information</p>
                
                <div class="space-y-6">
                    @if($order->buyer_name)
                        <div>
                            <p class="font-space text-[10px] uppercase tracking-widest text-[#888888] mb-2">Full Name</p>
                            <p class="font-manrope font-semibold text-lg text-[#1a1c1c] bg-[#f9f9f9] p-4 rounded">
                                {{ $order->buyer_name }}
                            </p>
                        </div>
                    @endif
                    
                    @if($order->buyer_phone)
                        <div>
                            <p class="font-space text-[10px] uppercase tracking-widest text-[#888888] mb-2">Phone Number</p>
                            <div class="bg-[#f9f9f9] p-4 rounded">
                                <a href="tel:{{ $order->buyer_phone }}" class="font-manrope font-semibold text-lg text-[#006a38] hover:underline">
                                    {{ $order->buyer_phone }}
                                </a>
                                <p class="font-manrope text-xs text-[#888888] mt-2">Click to call directly</p>
                            </div>
                        </div>
                    @endif
                    
                    @if($order->buyer_email)
                        <div>
                            <p class="font-space text-[10px] uppercase tracking-widest text-[#888888] mb-2">Email Address</p>
                            <div class="bg-[#f9f9f9] p-4 rounded">
                                <a href="mailto:{{ $order->buyer_email }}" class="font-manrope font-semibold text-lg text-[#006a38] hover:underline break-all">
                                    {{ $order->buyer_email }}
                                </a>
                                <p class="font-manrope text-xs text-[#888888] mt-2">Click to send email</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Address -->
            <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-8 border-l-4 border-[#0d9488]">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#006a38] mb-6">Delivery Address</p>
                
                @if($order->buyer_address)
                    <div class="bg-[#f9f9f9] p-6 rounded border-2 border-[#e0e0e0]">
                        <p class="font-manrope text-lg text-[#1a1c1c] leading-relaxed whitespace-pre-wrap">
                            {{ $order->buyer_address }}
                        </p>
                    </div>
                @else
                    <p class="font-manrope text-sm text-[#888888] italic">No delivery address provided</p>
                @endif
            </div>
        </div>

        <!-- Sidebar (1 col) -->
        <div class="space-y-6">
            <!-- Order Summary -->
            <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-8">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#006a38] mb-6">Order Summary</p>
                
                <div class="space-y-4">
                    <div class="flex justify-between pb-4 border-b border-[#f0f0f0]">
                        <span class="font-manrope text-sm text-[#888888]">Subtotal</span>
                        <span class="font-manrope font-semibold text-[#1a1c1c]">Rs. {{ number_format($order->total_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-space font-bold text-[#006a38] pt-4 border-t-2 border-[#006a38]">
                        <span>Total</span>
                        <span>Rs. {{ number_format($order->total_price, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Order Status -->
            <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-8">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#006a38] mb-6">Status</p>
                
                <div class="flex items-center gap-3">
                    <div class="w-4 h-4 rounded-full bg-[#006a38]"></div>
                    <span class="font-space font-bold text-lg text-[#006a38]">{{ ucfirst($order->status) }}</span>
                </div>
                
                <p class="font-manrope text-xs text-[#888888] mt-4">
                    Ordered on {{ $order->created_at->format('M d, Y \a\t h:i A') }}
                </p>
            </div>

            <!-- Buyer Profile -->
            @if($order->buyer)
                <div class="bg-white shadow-[0_20px_40px_rgba(26,28,28,0.06)] p-8">
                    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#006a38] mb-6">Buyer Profile</p>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="font-manrope text-sm text-[#888888]">Account Name</span>
                            <span class="font-manrope font-semibold text-[#1a1c1c]">{{ $order->buyer->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-manrope text-sm text-[#888888]">Email</span>
                            <span class="font-manrope font-semibold text-[#1a1c1c]">{{ $order->buyer->email }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-[#f9f9f9] p-8 rounded">
                <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#006a38] mb-6">Quick Actions</p>
                
                <div class="space-y-3">
                    @if($order->buyer_phone)
                        <a href="tel:{{ $order->buyer_phone }}" class="block w-full bg-[#006a38] text-white px-4 py-3 font-space font-bold text-xs uppercase tracking-wider text-center hover:bg-[#005030] rounded">
                            Call Buyer
                        </a>
                    @endif
                    
                    @if($order->buyer_email)
                        <a href="mailto:{{ $order->buyer_email }}" class="block w-full bg-[#0084D6] text-white px-4 py-3 font-space font-bold text-xs uppercase tracking-wider text-center hover:brightness-110 rounded">
                            Email Buyer
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
