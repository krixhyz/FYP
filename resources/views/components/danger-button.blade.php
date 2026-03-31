<button {{ $attributes->merge(['type' => 'button', 'class' => 'bg-transparent border-2 border-[#ba1a1a] text-[#ba1a1a] px-[22px] py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(186,26,26,0.06)] disabled:opacity-40 disabled:cursor-not-allowed transition-all']) }}>
    {{ $slot }}
</button>
