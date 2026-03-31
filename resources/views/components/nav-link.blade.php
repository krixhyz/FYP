@props(['active'])

@php
$classes = ($active ?? false)
            ? 'font-space text-xs font-medium uppercase tracking-wider text-[#006a38] px-3 py-2 border-b-2 border-[#006a38] transition-colors'
            : 'font-space text-xs font-medium uppercase tracking-wider text-[#444746] px-3 py-2 hover:text-[#006a38] transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
