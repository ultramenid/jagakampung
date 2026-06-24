<header class="bg-[#0f0f0f] text-white" style="background-image: radial-gradient(140% 200% at 0% 0%, #1f1f1f 0%, #0f0f0f 62%);">
    <div class="max-w-3xl px-4 mx-auto flex items-center justify-between py-3.5">
        <div class="flex items-center gap-3">
            <a href="{{ url('/cms/dashboard') }}" class="text-lg font-semibold tracking-tight text-white">
                Jagakampung<span class="font-mono text-gray-500">.id</span>
            </a>
            <span class="hidden sm:flex items-center gap-1.5 pl-3 border-l border-white/10 font-mono text-[10px] uppercase tracking-widest text-gray-400">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="motion-safe:animate-ping absolute inline-flex h-full w-full rounded-full bg-[#b8324a] opacity-60"></span>
                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-[#b8324a]"></span>
                </span>
                Pemantau aktif
            </span>
        </div>
        @include('partials.toogleprofile')
    </div>
</header>
