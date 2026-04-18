@extends('layouts.dashboard')

@section('content')
<!-- Header Section -->
<section class="px-0 md:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="font-space text-[12px] font-bold uppercase tracking-widest text-[#888] mb-2">Rental Details</p>
            <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-2">{{ $rental->product->title }}</h1>
            <p class="font-manrope text-base text-[#444746]">Complete information about your rental</p>
        </div>
        <span class="text-[12px] font-space font-bold px-4 py-2 rounded flex-shrink-0 whitespace-nowrap
            @if($rental->status === 'active') bg-[#d4edda] text-[#155724]
            @elseif(in_array($rental->status, ['completed', 'returned'], true)) bg-[#d1ecf1] text-[#0c5460]
            @elseif($rental->status === 'cancelled') bg-[#f8d7da] text-[#721c24]
            @else bg-[#e2e3e5] text-[#383d41]
            @endif">
            {{ ucfirst($rental->status) }}
        </span>
    </div>
</section>

<!-- Main Content Grid -->
<section class="px-0 md:px-8 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Product & Key Details -->
    <div class="lg:col-span-2">
        <!-- Product Card -->
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 mb-6">
            <h2 class="font-space font-bold text-lg text-[#1a1c1c] mb-4 uppercase tracking-widest">Item Details</h2>
            
            <div class="flex gap-6 mb-6 pb-6 border-b border-[rgba(189,202,189,0.1)]">
                <!-- Product Image -->
                <div class="w-32 h-32 bg-[#e2e2e2] rounded-lg overflow-hidden flex-shrink-0">
                    @php
                        $productImage = collect($rental->product?->images ?? [])->first();
                        $productImagePath = data_get($productImage, 'path', is_string($productImage) ? $productImage : null) ?? $rental->product?->image;
                    @endphp
                    @if($productImagePath)
                        <img src="{{ asset('storage/' . $productImagePath) }}" alt="{{ $rental->product->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-[#888]">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2 0l1.586-1.586a2 2 0 012.828 0L20 16m-6-2l1.586-1.586a2 2 0 012.828 0L16 8"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <a href="{{ route('products.show', $rental->product) }}" class="font-space font-bold text-xl text-[#006a38] hover:underline mb-2 block">
                        {{ $rental->product->title }}
                    </a>
                    <p class="text-sm text-[#888] mb-3">Product ID: {{ $rental->product->id }}</p>
                    
                    @if($rental->product->description)
                        <p class="font-manrope text-sm text-[#666] line-clamp-3">{{ $rental->product->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Rental Period Timeline -->
            <div class="space-y-4">
                <h3 class="font-space font-bold text-base text-[#1a1c1c] uppercase tracking-widest">Rental Timeline</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-[#f9f9f9] p-4 rounded-lg border border-[rgba(189,202,189,0.1)]">
                        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Start Date</p>
                        <p class="font-space font-bold text-lg text-[#006a38]">{{ $rental->start_date->format('M d, Y') }}</p>
                        <p class="text-sm text-[#888] mt-1">{{ $rental->start_date->format('h:i A') }}</p>
                    </div>

                    <div class="bg-[#f9f9f9] p-4 rounded-lg border border-[rgba(189,202,189,0.1)]">
                        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">End Date</p>
                        <p class="font-space font-bold text-lg 
                            @if($rental->status === 'active' && $rental->end_date->isPast()) text-[#ba1a1a]
                            @elseif(in_array($rental->status, ['completed', 'returned'], true)) text-[#0c5460]
                            @else text-[#006a38]
                            @endif">
                            {{ $rental->end_date->format('M d, Y') }}
                        </p>
                        <p class="text-sm text-[#888] mt-1">{{ $rental->end_date->format('h:i A') }}</p>
                    </div>
                </div>

                <div class="bg-[#f9f9f9] p-4 rounded-lg border border-[rgba(189,202,189,0.1)]">
                    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Duration</p>
                    <p class="font-space font-bold text-lg text-[#1a1c1c]">{{ $rental->duration }} {{ ucfirst($rental->rent_type) }}(s)</p>
                </div>

                @if($rental->returned_at)
                    <div class="bg-[#f9f9f9] p-4 rounded-lg border border-[rgba(189,202,189,0.1)]">
                        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Returned On</p>
                        <p class="font-space font-bold text-lg text-[#0c5460]">{{ $rental->returned_at->format('M d, Y') }}</p>
                        <p class="text-sm text-[#888] mt-1">{{ $rental->returned_at->format('h:i A') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment Details Card -->
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6">
            <h2 class="font-space font-bold text-lg text-[#1a1c1c] mb-4 uppercase tracking-widest">Payment Details</h2>
            
            <div class="space-y-3 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                <div class="flex items-center justify-between">
                    <span class="text-[#888]">Rental Rate:</span>
                    <span class="font-space font-bold text-[#006a38]">Rs. {{ number_format($rental->rent_fare, 2) }} / {{ ucfirst($rental->rent_type) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[#888]">Deposit:</span>
                    <span class="font-space font-bold text-[#d97706]">Rs. {{ number_format($rental->rent_deposit, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[#888]">Payment Status:</span>
                    <span class="text-[11px] font-space font-bold px-3 py-1 rounded
                        @if($rental->payment_status === 'paid') bg-[#d4edda] text-[#155724]
                        @elseif($rental->payment_status === 'pending') bg-[#fff3cd] text-[#856404]
                        @elseif($rental->payment_status === 'refunded') bg-[#d1ecf1] text-[#0c5460]
                        @else bg-[#e2e3e5] text-[#383d41]
                        @endif">
                        {{ ucfirst($rental->payment_status) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4">
                <span class="font-space font-bold text-base text-[#1a1c1c]">Total Amount</span>
                <span class="font-space font-bold text-2xl text-[#006a38]">Rs. {{ number_format($rental->total_amount ?? ($rental->rent_fare + $rental->rent_deposit), 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Right Column: People & Actions -->
    <div class="lg:col-span-1">
        <!-- Participants Card -->
        <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 mb-6">
            <h2 class="font-space font-bold text-lg text-[#1a1c1c] mb-4 uppercase tracking-widest">Participants</h2>
            
            @if(Auth::id() === $rental->renter_id)
                <!-- Renter's view: Show owner -->
                <div class="mb-4 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Owner</p>
                    <p class="font-space font-bold text-[#1a1c1c] mb-1">{{ $rental->owner->name }}</p>
                    <p class="text-sm text-[#888]">{{ $rental->owner->email }}</p>
                </div>
            @elseif(Auth::id() === $rental->owner_id)
                <!-- Owner's view: Show renter -->
                <div class="mb-4 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Renter</p>
                    <p class="font-space font-bold text-[#1a1c1c] mb-1">{{ $rental->renter->name }}</p>
                    <p class="text-sm text-[#888]">{{ $rental->renter->email }}</p>
                </div>
            @else
                <!-- Admin/Other view: Show both -->
                <div class="mb-4 pb-4 border-b border-[rgba(189,202,189,0.1)]">
                    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Renter</p>
                    <p class="font-space font-bold text-[#1a1c1c] mb-1">{{ $rental->renter->name }}</p>
                    <p class="text-sm text-[#888]">{{ $rental->renter->email }}</p>
                </div>
                <div>
                    <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#888] mb-2">Owner</p>
                    <p class="font-space font-bold text-[#1a1c1c] mb-1">{{ $rental->owner->name }}</p>
                    <p class="text-sm text-[#888]">{{ $rental->owner->email }}</p>
                </div>
            @endif
        </div>

        <!-- Actions Card -->
        @if(Auth::id() === $rental->renter_id)
            <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 mb-6">
                <h2 class="font-space font-bold text-lg text-[#1a1c1c] mb-4 uppercase tracking-widest">Actions</h2>
                
                <div class="space-y-2">
                    @if(!$rental->return_requested_at && $rental->status === 'active')
                        <form action="{{ route('rental.requestReturn', $rental) }}" method="POST" class="block">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-[#006a38] hover:bg-[#004e26] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded transition-all">
                                Mark Returned
                            </button>
                        </form>
                    @elseif($rental->return_requested_at)
                        <div class="rounded-lg border border-[#ffd580] bg-[#fffbeb] px-4 py-3 text-sm text-[#92400e]">
                            Return requested on {{ $rental->return_requested_at->format('M d, Y h:i A') }}
                        </div>
                    @endif

                    <a href="{{ route('review.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="block text-center bg-[#f0f8f5] border border-[#d97706] text-[#d97706] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(217,119,6,0.06)] transition-all">
                        Leave Review
                    </a>

                    <a href="{{ route('dispute.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="block text-center bg-[#f9f9f9] border border-[#ba1a1a] text-[#ba1a1a] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(186,26,26,0.06)] transition-all">
                        Report Issue
                    </a>
                </div>
            </div>
        @elseif(Auth::id() === $rental->owner_id)
            <div class="bg-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-[rgba(189,202,189,0.1)] p-6 mb-6">
                <h2 class="font-space font-bold text-lg text-[#1a1c1c] mb-4 uppercase tracking-widest">Actions</h2>

                @if($rental->return_requested_at && $rental->status === 'active')
                    <div class="mb-4 rounded-lg border border-[#ffd580] bg-[#fffbeb] px-4 py-3 text-sm text-[#92400e]">
                        Return requested by renter on {{ $rental->return_requested_at->format('M d, Y h:i A') }}.
                    </div>

                    <div class="space-y-2">
                        <a href="{{ route('dispute.create', ['type' => 'rental', 'id' => $rental->id]) }}" class="block text-center bg-[#f8d7da] border border-[#ba1a1a] text-[#721c24] px-4 py-2 font-space text-[10px] font-bold uppercase rounded hover:bg-[rgba(186,26,26,0.06)] transition-all">
                            Report Damage to Admin
                        </a>

                        <form action="{{ route('rental.return', $rental) }}" method="POST" class="block">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-[#006a38] hover:bg-[#004e26] text-white px-4 py-2 font-space text-[10px] font-bold uppercase rounded transition-all">
                                Mark as Returned
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-sm text-[#666]">Waiting for the renter to request return.</p>
                @endif
            </div>
        @endif

        <!-- Info Card -->
        <div class="bg-[#f9f9f9] rounded-lg border border-[rgba(189,202,189,0.1)] p-4">
            <p class="text-xs text-[#888] leading-relaxed">
                <strong>Rental ID:</strong><br>
                {{ $rental->id }}
            </p>
            <p class="text-xs text-[#888] leading-relaxed mt-3">
                <strong>Created:</strong><br>
                {{ $rental->created_at->format('M d, Y') }}
            </p>
        </div>
    </div>
</section>

<!-- Navigation -->
<section class="px-0 md:px-8 py-6">
    <a href="{{ route('rental.myRentals') }}" class="inline-flex items-center gap-2 text-[#006a38] font-space font-bold text-sm hover:gap-3 transition-all">
        ← Back to My Rentals
    </a>
</section>
@endsection
