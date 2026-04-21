<button {{ $attributes->merge(['type' => 'button', 'class' => 'bg-transparent border-2 border-[#006a38] text-[#006a38] px-[22px] py-[10px] font-space font-bold text-sm uppercase tracking-wider hover:bg-[rgba(0,106,56,0.06)] disabled:opacity-40 disabled:cursor-not-allowed transition-all']) }}>
    {{ $slot }}
</button>
