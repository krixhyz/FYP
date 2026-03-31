@extends('layouts.admin')

@section('title', 'Content Moderation')

@section('content')
<div class="surface-card-strong p-6 md:p-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="section-kicker">Admin Safety</p>
            <h2 class="section-title mt-1">Content Moderation</h2>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="category" class="input-field !py-2 text-sm">
                <option value="">Filter by Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ ucfirst($category) }}</option>
                @endforeach
            </select>
            <button class="btn-pill btn-pill-soft !px-4 !py-2 text-sm">Filter</button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($products as $product)
            <article class="surface-card p-5 {{ $product->flagged ? 'border-2 border-[#ba1a1a] bg-[#fee2e2]' : 'border-2 border-amber-300 bg-amber-50' }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xl font-extrabold">{{ $product->title }}</h3>
                            <span class="status-chip status-error {{ $product->flagged ? '' : 'opacity-0' }}">High Priority</span>
                        </div>
                        <p class="meta-text mt-1">Seller: {{ $product->user?->name ?? 'N/A' }} | Category: {{ ucfirst($product->category ?? 'general') }}</p>
                    </div>
                </div>

                <div class="mt-3 bg-white px-3 py-2 text-sm border border-neutral-300">
                    {{ $product->flagged ? 'Possible policy violation reported.' : 'Awaiting content verification.' }}
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.content.decision', $product) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="decision" value="approve">
                        <button class="btn-pill btn-pill-dark !px-4 !py-2 text-sm">Approve</button>
                    </form>

                    <form method="POST" action="{{ route('admin.content.decision', $product) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="decision" value="flag">
                        <button class="btn-pill btn-pill-soft !px-4 !py-2 text-sm">Flag Listing</button>
                    </form>

                    <form method="POST" action="{{ route('admin.content.decision', $product) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="decision" value="reject">
                        <button class="btn-pill !border-[#ba1a1a] !text-[#ba1a1a] !px-4 !py-2 text-sm hover:!bg-[#ba1a1a] hover:!text-white">Reject</button>
                    </form>

                    <a href="{{ route('products.show', $product->id) }}" class="btn-pill btn-pill-soft !px-4 !py-2 text-sm">View Full Details</a>
                </div>
            </article>
        @empty
            <p class="meta-text">No listings pending moderation.</p>
        @endforelse
    </div>

    <div class="mt-5">{{ $products->links() }}</div>
</div>
@endsection
