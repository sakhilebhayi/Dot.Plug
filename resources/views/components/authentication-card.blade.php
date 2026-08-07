<div class="relative min-h-screen flex flex-col justify-center items-center px-5 py-12 overflow-hidden" style="background: radial-gradient(ellipse 80% 60% at 15% 0%, rgba(107,76,147,0.16) 0%, transparent 60%), var(--ink);">
    {{-- Same signature element as welcome.blade.php's hero — line-art plug + sockets, echoing
    the logo's own plug icon and "plug into any Dot platform." --}}
    <svg class="hidden lg:block absolute right-[6%] bottom-[10%] h-[50%] w-auto opacity-[0.13] pointer-events-none" viewBox="0 0 260 300" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M60 280L120 220" stroke="#f1eef5" stroke-width="4" stroke-linecap="round"/>
        <path d="M110 190C90 210 90 240 110 260C130 280 160 280 180 260L200 240L130 170L110 190Z" stroke="#f1eef5" stroke-width="3" stroke-linejoin="round"/>
        <path d="M170 130L140 160M210 170L180 200" stroke="#f1eef5" stroke-width="4" stroke-linecap="round"/>
        <rect x="30" y="40" width="34" height="46" rx="4" stroke="#f0c33a" stroke-width="2.5"/>
        <circle cx="40" cy="56" r="2.5" fill="#f0c33a"/>
        <circle cx="54" cy="56" r="2.5" fill="#f0c33a"/>
        <rect x="90" y="30" width="34" height="46" rx="4" stroke="#f0c33a" stroke-width="2.5" opacity="0.6"/>
        <circle cx="100" cy="46" r="2.5" fill="#f0c33a" opacity="0.6"/>
        <circle cx="114" cy="46" r="2.5" fill="#f0c33a" opacity="0.6"/>
    </svg>

    <div class="relative z-10 mb-8">
        {{ $logo }}
    </div>

    <div class="relative z-10 w-full sm:max-w-md px-6 py-8 sm:px-8 bg-[var(--ink-soft)] border border-[var(--line)] rounded-2xl shadow-xl">
        {{ $slot }}
    </div>
</div>
