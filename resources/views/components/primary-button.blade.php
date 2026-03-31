<button {{ $attributes->merge(['type' => 'submit', 'class' => 'bg-gradient-to-br from-[#006a38] to-[#09864a] text-white px-6 py-3 font-space font-bold text-sm uppercase tracking-wider border-0 hover:brightness-110 active:brightness-95 disabled:opacity-40 disabled:cursor-not-allowed transition-all']) }}>
    {{ $slot }}
</button>
