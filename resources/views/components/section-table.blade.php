@props(['data', 'empty' => 'No records found.'])

<table class="w-full border-collapse text-left text-sm text-[var(--reloop-ink-soft)]">
    <thead class="bg-[var(--reloop-primary-soft)] text-xs uppercase text-[var(--reloop-ink)]">
        {{ $header }}
    </thead>

    <tbody>
        @if ($data->isEmpty())
            <tr>
                <td colspan="10" class="py-6 text-center text-[var(--reloop-ink-soft)]">{{ $empty }}</td>
            </tr>
        @else
            {{ $slot }}
        @endif
    </tbody>
</table>
