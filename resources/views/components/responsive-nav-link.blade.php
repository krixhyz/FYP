@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block px-4 py-3 font-space text-sm uppercase tracking-wider text-[#006a38] border-b border-[rgba(189,202,189,0.2)] hover:text-[#006a38] min-h-[44px] flex items-center transition-colors'
            : 'block px-4 py-3 font-space text-sm uppercase tracking-wider text-[#444746] border-b border-[rgba(189,202,189,0.2)] hover:text-[#006a38] min-h-[44px] flex items-center transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
