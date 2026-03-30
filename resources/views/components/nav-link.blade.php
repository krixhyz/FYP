@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-[var(--reloop-primary)] px-1 pt-1 text-sm font-semibold leading-5 text-[var(--reloop-primary-dark)] transition'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-[var(--reloop-ink-soft)] transition hover:text-[var(--reloop-ink)] hover:border-[var(--reloop-border)] focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
