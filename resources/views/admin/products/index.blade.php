@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="surface-card-strong">
    <div class="p-5 border-b border-neutral-200 flex items-center justify-between">
        <h2 class="section-title">Manage Products</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-neutral-100 text-neutral-700 uppercase text-xs tracking-[0.12em]">
                <tr>
                    <th class="p-3 text-left">Title</th>
                    <th class="p-3 text-left">Owner</th>
                    <th class="p-3 text-left">Flagged</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200">
                @foreach ($products as $product)
                    <tr>
                        <td class="p-3 font-semibold">{{ $product->title }}</td>
                        <td class="p-3 text-neutral-700">{{ $product->user->name ?? 'N/A' }}</td>
                        <td class="p-3">
                            @if($product->flagged)
                                <span class="status-chip status-error">Yes</span>
                            @else
                                <span class="status-chip status-neutral">No</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                @if(! $product->flagged)
                                <form method="POST" action="{{ route('admin.products.flag', $product) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn-pill !px-3 !py-1 text-xs !border-amber-700 !text-amber-700 hover:!bg-amber-700 hover:!text-white">Flag</button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.products.unflag', $product) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn-pill btn-pill-soft !px-3 !py-1 text-xs">Unflag</button>
                                </form>
                                @endif
                                <a href="{{ route('admin.products.show', $product) }}" class="btn-pill !px-3 !py-1 text-xs !border-blue-600 !text-blue-600 hover:!bg-blue-600 hover:!text-white">View</a>
                                <form method="POST" action="{{ route('admin.products.delete', $product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-pill !px-3 !py-1 text-xs !border-red-600 !text-red-600 hover:!bg-red-600 hover:!text-white"
                                            onclick="return confirm('Delete product?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-4">
        {{ $products->links() }}
    </div>
</div>
@endsection