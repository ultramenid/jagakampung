@php
    // Shared-edge cadastral grid: 4 cols × 5 rows of jittered corners → 12 parcels.
    // Sharing corner coordinates guarantees no gaps between plots (a real survey).
    $grid = [
        [[60, 90], [205, 72], [360, 84], [500, 80]],
        [[54, 215], [222, 200], [350, 222], [505, 208]],
        [[66, 338], [210, 355], [368, 340], [498, 352]],
        [[58, 490], [225, 478], [355, 498], [506, 485]],
        [[62, 612], [208, 628], [362, 616], [500, 624]],
    ];
    $active  = ['0-1', '2-0'];   // contested — oxblood, pulsing
    $potensi = ['1-2'];          // monitored — steel

    $parcels = [];
    foreach (range(0, 3) as $r) {
        foreach (range(0, 2) as $c) {
            $key = "$r-$c";
            $pts = [$grid[$r][$c], $grid[$r][$c + 1], $grid[$r + 1][$c + 1], $grid[$r + 1][$c]];
            $cx = array_sum(array_column($pts, 0)) / 4;
            $cy = array_sum(array_column($pts, 1)) / 4;
            $parcels[] = [
                'points' => implode(' ', array_map(fn ($p) => "$p[0],$p[1]", $pts)),
                'cx'     => round($cx),
                'cy'     => round($cy),
                'code'   => chr(65 + $c) . '·' . sprintf('%02d', $r * 3 + $c + 1),
                'status' => in_array($key, $active) ? 'active' : (in_array($key, $potensi) ? 'potensi' : 'surveyed'),
            ];
        }
    }
@endphp

<div class="jkl-root relative flex min-h-screen items-center justify-center overflow-hidden p-6">
    <style>
        .jkl-root { background: radial-gradient(130% 100% at 50% 0%, #1f1f1f 0%, #0f0f0f 55%, #0a0a0a 100%); }
        .jkl-scrim {
            background:
                radial-gradient(60% 50% at 50% 50%, rgba(10,10,10,.55) 0%, rgba(10,10,10,.78) 100%);
        }
        .jkl-card { background: rgba(250,250,250,.98); }
        .jkl-rise { opacity: 0; animation: jkl-rise .6s cubic-bezier(.22,1,.36,1) forwards; animation-delay: var(--d, 0s); }
        @keyframes jkl-rise { from { opacity: 0; transform: translateY(9px); } to { opacity: 1; transform: none; } }
        .jkl-contested { animation: jkl-pulse 3.4s ease-in-out infinite; }
        @keyframes jkl-pulse { 0%, 100% { fill-opacity: .10; } 50% { fill-opacity: .26; } }
        @media (prefers-reduced-motion: reduce) {
            .jkl-rise { opacity: 1; animation: none; }
            .jkl-contested { animation: none; fill-opacity: .2; }
        }
    </style>

    {{-- ───────── situation board (the signature) — full-viewport backdrop ───────── --}}
    <svg class="absolute inset-0 h-full w-full" viewBox="0 0 560 720"
         preserveAspectRatio="xMidYMid slice" aria-hidden="true">
        @for ($x = 0; $x <= 560; $x += 80)
            <line x1="{{ $x }}" y1="0" x2="{{ $x }}" y2="720" stroke="rgba(255,255,255,.045)" />
        @endfor
        @for ($y = 0; $y <= 720; $y += 80)
            <line x1="0" y1="{{ $y }}" x2="560" y2="{{ $y }}" stroke="rgba(255,255,255,.045)" />
        @endfor
        @foreach ($parcels as $p)
            @if ($p['status'] === 'active')
                <polygon class="jkl-contested" points="{{ $p['points'] }}"
                         fill="#890620" fill-opacity=".16" stroke="#b8324a" stroke-opacity=".7"
                         stroke-width="1.4" vector-effect="non-scaling-stroke" />
                <text x="{{ $p['cx'] }}" y="{{ $p['cy'] }}" fill="#e7a3ad"
                      font-family="Geist Mono, monospace" font-size="12" font-weight="500" text-anchor="middle">{{ $p['code'] }}</text>
            @elseif ($p['status'] === 'potensi')
                <polygon points="{{ $p['points'] }}" fill="#348aa7" fill-opacity=".12"
                         stroke="#348aa7" stroke-opacity=".5" stroke-width="1.2" vector-effect="non-scaling-stroke" />
                <text x="{{ $p['cx'] }}" y="{{ $p['cy'] }}" fill="#7fc3d6"
                      font-family="Geist Mono, monospace" font-size="11" text-anchor="middle">{{ $p['code'] }}</text>
            @else
                <polygon points="{{ $p['points'] }}" fill="#ffffff" fill-opacity=".015"
                         stroke="#605b51" stroke-opacity=".5" stroke-width="1" vector-effect="non-scaling-stroke" />
                <text x="{{ $p['cx'] }}" y="{{ $p['cy'] }}" fill="rgba(255,255,255,.26)"
                      font-family="Geist Mono, monospace" font-size="11" text-anchor="middle">{{ $p['code'] }}</text>
            @endif
        @endforeach
    </svg>
    <div class="jkl-scrim absolute inset-0"></div>

    {{-- top corner readout — anchors the board as a live survey --}}
    <div class="jkl-rise absolute left-6 top-6 right-6 z-10 flex items-center justify-between font-mono text-[10px] uppercase tracking-widest text-gray-500">
        <span>02°33′S&nbsp;&nbsp;112°42′E · Seruyan</span>
        <span class="hidden sm:flex items-center gap-4">
            <span class="flex items-center gap-1.5"><span class="inline-block h-2 w-2 rounded-full bg-[#b8324a]"></span> Aktif</span>
            <span class="flex items-center gap-1.5"><span class="inline-block h-2 w-2 rounded-full border border-[#348aa7]"></span> Potensi</span>
        </span>
    </div>

    {{-- ───────────────────────────── centered access card ───────────────────────────── --}}
    <div class="jkl-card jkl-rise relative z-10 w-full max-w-sm rounded-xl border border-white/10 p-8 shadow-2xl shadow-black/40" style="--d:.08s">
        <p class="font-mono text-[11px] uppercase tracking-[0.2em] text-gray-400">Akses Panel · CMS</p>
        <a href="/" class="mt-2 block text-2xl font-semibold tracking-tight text-gray-900">
            Jagakampung<span class="font-mono text-gray-400">.id</span>
        </a>
        <p class="mt-2 text-sm text-gray-500">Masuk untuk mengelola data konflik agraria.</p>

        @if (session()->has('error'))
            <div role="alert" class="mt-6 rounded-md border border-red-100 bg-red-50 px-3 py-2.5 text-xs text-red-600">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="login" class="mt-7 space-y-5">
            <div>
                <label for="login-email" class="gk-label">Email</label>
                <input id="login-email" wire:model="email" type="email" autocomplete="email"
                       class="gk-input @error('email') border-red-300 @enderror"
                       placeholder="nama@contoh.com">
                @error('email') <p class="gk-error" id="login-email-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="login-password" class="gk-label">Kata sandi</label>
                <input id="login-password" wire:model="password" type="password" autocomplete="current-password"
                       class="gk-input @error('password') border-red-300 @enderror"
                       placeholder="••••••••">
                @error('password') <p class="gk-error" id="login-password-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" wire:target="login" wire:loading.attr="disabled" class="gk-btn-primary w-full">
                <svg wire:loading wire:target="login" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="login">Masuk</span>
                <span wire:loading wire:target="login">Memproses…</span>
            </button>
        </form>
    </div>

    <p class="jkl-rise absolute bottom-6 left-0 right-0 z-10 text-center font-mono text-[10px] uppercase tracking-widest text-gray-600" style="--d:.16s">
        Akses terbatas untuk pemantau terverifikasi
    </p>
</div>
