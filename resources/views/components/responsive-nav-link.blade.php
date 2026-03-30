@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-[var(--reloop-primary)] bg-[var(--reloop-primary-soft)] py-2 ps-3 pe-4 text-start text-base font-semibold text-[var(--reloop-primary-dark)] transition focus:outline-none'
            : 'block w-full border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-[var(--reloop-ink-soft)] transition hover:border-[var(--reloop-border)] hover:bg-[var(--reloop-primary-soft)]/40 hover:text-[var(--reloop-ink)] focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
