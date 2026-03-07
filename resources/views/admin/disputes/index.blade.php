@extends('layouts.admin')
@section('title', 'Disputes')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-lg">Disputes</h2>
    <form method="GET" class="flex gap-2">
        <select name="status" onchange="this.form.submit()"
                class="border border-gray-300 rounded px-3 py-1.5 text-sm">
            <option value="">All statuses</option>
            @foreach(['open','in_review','resolved','dismissed'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="p-3 text-left">#</th>
                    <th class="p-3 text-left">Reporter</th>
                    <th class="p-3 text-left">Subject</th>
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Filed</th>
                    <th class="p-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($disputes as $dispute)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 text-gray-400">{{ $dispute->id }}</td>
                        <td class="p-3 font-medium">{{ $dispute->reporter?->name ?? 'N/A' }}</td>
                        <td class="p-3 max-w-xs truncate">{{ $dispute->subject }}</td>
                        <td class="p-3 capitalize">{{ $dispute->transaction_type }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $dispute->statusBadge() }}">
                                {{ ucfirst(str_replace('_',' ',$dispute->status)) }}
                            </span>
                        </td>
                        <td class="p-3 text-gray-400 text-xs">{{ $dispute->created_at->diffForHumans() }}</td>
                        <td class="p-3">
                            <a href="{{ route('admin.disputes.show', $dispute) }}"
                               class="text-indigo-600 hover:underline text-xs font-medium">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-5 text-center text-gray-400">No disputes found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $disputes->links() }}</div>
@endsection
