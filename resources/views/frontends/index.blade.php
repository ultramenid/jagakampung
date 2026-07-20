@extends('layouts.mapLayout')


@section('content')
    <div class="sm:flex hidden" x-data="{ legend: true }">

        {{-- Left rail — field dossier of land under dispute --}}
        @php
            $aktifPct = $stats['total'] ? round($stats['aktif'] / $stats['total'] * 100) : 0;
            $potensiPct = $stats['total'] ? 100 - $aktifPct : 0;
            $layers = [
                ['id' => 'adminkabkota', 'name' => 'Titik Konflik', 'checked' => true],
                ['id' => 'kawasanhutan', 'name' => 'Kawasan Hutan', 'checked' => false],
                ['id' => 'pbph', 'name' => 'Konsesi PBPH', 'checked' => false],
            ];
        @endphp
        <aside class="w-80 h-screen flex flex-col border-r border-gray-200 bg-white">

            {{-- Masthead — same dark console chrome as the CMS + login --}}
            <div class="flex-shrink-0 px-6 pt-6 pb-5 bg-[#0f0f0f] text-white" style="background-image: radial-gradient(140% 200% at 0% 0%, #1f1f1f 0%, #0f0f0f 62%);">
                <a href="#" class="text-xl font-semibold tracking-tight text-white">
                    Jagakampung<span class="font-mono text-gray-500">.id</span>
                </a>
                <div class="mt-1.5 flex items-center gap-2">
                    <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-gray-400">Peta Konflik </span>
                    <span class="relative flex h-1.5 w-1.5" title="">
                        <span class="motion-safe:animate-ping absolute inline-flex h-full w-full rounded-full bg-[#b8324a] opacity-60"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-[#b8324a]"></span>
                    </span>
                </div>
            </div>

            {{-- Caseload — the thesis: how big, how active --}}
            <div class="flex-shrink-0 px-6 pt-5 pb-6 border-b border-gray-100">
                <div class="flex items-baseline justify-between font-mono text-[10px] uppercase tracking-wider text-gray-400">
                    <span>Status konflik</span>
                    <span class="tabular-nums">{{ $stats['total'] }} titik · {{ $stats['provinsi'] }} provinsi</span>
                </div>

                {{-- composition bar: segment widths = real aktif/potensi share --}}
                <div class="mt-2.5 flex h-2 rounded-full overflow-hidden bg-gray-100">
                    <div class="bg-status-aktif" style="width: {{ $aktifPct }}%;"></div>
                    <div class="bg-status-potensi" style="width: {{ $potensiPct }}%;"></div>
                </div>
                <div class="mt-2.5 flex items-center gap-5 font-mono text-[11px] text-gray-500">
                    <span><span class="tabular-nums font-semibold text-status-aktif">{{ $stats['aktif'] }}</span> Aktif</span>
                    <span><span class="tabular-nums font-semibold text-status-potensi">{{ $stats['potensi'] }}</span> Potensi</span>
                </div>

                {{-- scale: land + people --}}
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-end gap-6">
                    <div>
                        <p class="font-mono text-3xl text-gray-900 tabular-nums leading-none">{{ number_format(round($stats['luas']), 0, '.', ',') }}</p>
                        <p class="mt-1.5 text-[11px] text-gray-400">Hektar terdampak</p>
                    </div>
                    <div>
                        <p class="font-mono text-3xl text-gray-900 tabular-nums leading-none">{{ (int) $stats['kk'] }}</p>
                        <p class="mt-1.5 text-[11px] text-gray-400">KK terdampak</p>
                    </div>
                </div>
            </div>

            {{-- Layers --}}
            <div class="flex-shrink-0 px-6 py-5 border-b border-gray-100">
                <p class="font-mono text-[10px] uppercase tracking-wider text-gray-400 mb-2">Layers</p>
                <div class="flex flex-col">
                    @foreach ($layers as $l)
                        <label class="flex items-center gap-2.5 px-2 py-1.5 -mx-2 rounded-md hover:bg-gray-50 has-[:focus-visible]:bg-gray-50 transition cursor-pointer select-none">
                            <span class="flex-1 text-[13px] text-gray-700">{{ __($l['name']) }}</span>
                            <span class="relative flex flex-shrink-0">
                                <input type="checkbox" id="{{ $l['id'] }}" class="peer sr-only" @checked($l['checked'])>
                                <span class="block h-4 w-7 rounded-full bg-gray-200 peer-checked:bg-accent-500 peer-focus-visible:ring-2 peer-focus-visible:ring-accent-500/40 transition-colors"></span>
                                <span class="absolute w-2.5 h-2.5 bg-white rounded-full left-[3px] top-[3px] peer-checked:translate-x-full transition"></span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Case list --}}
            <div class="flex-1 flex flex-col min-h-0">
                <div class="flex-shrink-0 px-6 pt-5 pb-2 flex items-baseline justify-between">
                    <p class="font-mono text-[10px] uppercase tracking-wider text-gray-400">Daftar Konflik</p>
                    <span class="font-mono text-[10px] text-gray-300 tabular-nums">{{ count($konfliks) }}</span>
                </div>
                <div class="flex-1 overflow-y-auto px-3 pb-4">
                    @forelse ($konfliks as $k)
                        @php $isAktif = $k->status === 'aktif'; @endphp
                        <button type="button"
                            onclick="focusKonflik({{ $k->id }}, {{ $k->lat }}, {{ $k->long }})"
                            class="w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-gray-50 focus-visible:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/40 transition group">
                            <span aria-hidden="true" class="w-2 h-2 rounded-full flex-shrink-0 {{ $isAktif ? 'bg-status-aktif' : 'bg-status-potensi' }}"></span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-baseline justify-between gap-2">
                                    <span class="text-[13px] font-medium text-gray-900 truncate group-hover:text-gray-950">
                                        {{ $k->desa ?: $k->kecamatan ?: $k->kabkota ?: 'Tanpa nama' }}
                                    </span>
                                    <span class="font-mono text-[9px] uppercase tracking-wider flex-shrink-0 {{ $isAktif ? 'text-status-aktif' : 'text-status-potensi' }}">{{ $k->status }}</span>
                                </span>
                                <span class="block text-[11px] text-gray-400 truncate">{{ $k->kabkota }}{{ $k->provinsi ? ', '.$k->provinsi : '' }}</span>
                                <span class="block mt-1 font-mono text-[10px] text-gray-400 tabular-nums">
                                    {{ number_format($k->luas, 0, '.', ',') }} ha · {{ number_format($k->kk ?? 0, 0, '.', ',') }} KK
                                </span>
                            </span>
                        </button>
                    @empty
                        <p class="px-3 py-6 text-center text-xs text-gray-400">Belum ada data konflik.</p>
                    @endforelse
                </div>
            </div>

            {{-- Provenance --}}
            <div class="flex-shrink-0 px-6 py-3 border-t border-gray-100">
                <p class="font-mono text-[10px] text-gray-300 tracking-wide">Data · Auriga Nusantara</p>
            </div>
        </aside>

        <div id="map" role="application" aria-label="Peta konflik " class="flex-1 h-screen"></div>

        {{-- Map Legend --}}
        <div role="group" aria-label="Legenda status konflik"
            class="fixed bottom-8 left-[calc(50%+10rem)] -translate-x-1/2 flex z-[9999] items-center gap-3 bg-white/90 backdrop-blur-sm border border-gray-200 rounded-full px-5 py-2.5 shadow-geist font-mono text-[11px] uppercase tracking-wider text-gray-600 select-none">

            <button id="toggleAktif" aria-pressed="true" aria-label="Tampilkan titik Aktif" class="legend-btn flex items-center gap-1.5 cursor-pointer rounded-full px-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/40">
                <span aria-hidden="true" class="w-3 h-3 rounded-full bg-status-aktif border-2 border-white shadow-sm inline-block"></span>
                Aktif
            </button>

            <span aria-hidden="true" class="w-px h-3 bg-gray-200"></span>

            <button id="togglePotensi" aria-pressed="true" aria-label="Tampilkan titik Potensi" class="legend-btn flex items-center gap-1.5 cursor-pointer rounded-full px-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/40">
                <span aria-hidden="true" class="w-3 h-3 rounded-full bg-white border-[3px] border-status-potensi shadow-sm inline-block"></span>
                Potensi
            </button>
        </div>

        {{-- Layer legends — shown only while their WMS layer is toggled on --}}
        @php
            $pbphLegend = [
                ['label' => 'PBPH-HT (HHK-HT)', 'color' => '#e9c46a'],
                ['label' => 'PBPH-HA (HHK-HA)', 'color' => '#2a9d8f'],
                ['label' => 'PBPH-RE', 'color' => '#264653'],
            ];
            $kawasanhutanLegend = [
                ['label' => 'APL', 'color' => '#fef9f1'],
                ['label' => 'Hutan Lindung (HL)', 'color' => '#01ad00'],
                ['label' => 'Hutan Produksi (HP)', 'color' => '#ffff00'],
                ['label' => 'HP Konversi (HPK)', 'color' => '#ff5eff'],
                ['label' => 'HP Terbatas (HPT)', 'color' => '#8af200'],
                ['label' => 'KSA/KPA', 'color' => '#ad40ff'],
                ['label' => 'KSA/KPA Air', 'color' => '#ad40ff'],
                ['label' => 'Tubuh Air', 'color' => '#0000ff'],
            ];
        @endphp
        <div id="wmsLegends" class="fixed top-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none">
            <div id="kawasanhutan-legend" class="hidden pointer-events-auto bg-white/90 backdrop-blur-sm border border-gray-200 rounded-xl px-4 py-3 shadow-geist text-[11px] text-gray-600 min-w-40">
                <p class="font-mono text-[10px] uppercase tracking-wider text-gray-400 mb-2">Kawasan Hutan</p>
                <div class="flex flex-col gap-1.5">
                    @foreach ($kawasanhutanLegend as $item)
                        <span class="flex items-center gap-2">
                            <span aria-hidden="true" class="w-3 h-3 rounded-sm border border-black/10 flex-shrink-0" style="background:{{ $item['color'] }}"></span>
                            {{ $item['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
            <div id="pbph-legend" class="hidden pointer-events-auto bg-white/90 backdrop-blur-sm border border-gray-200 rounded-xl px-4 py-3 shadow-geist text-[11px] text-gray-600 min-w-40">
                <p class="font-mono text-[10px] uppercase tracking-wider text-gray-400 mb-2">Konsesi PBPH</p>
                <div class="flex flex-col gap-1.5">
                    @foreach ($pbphLegend as $item)
                        <span class="flex items-center gap-2">
                            <span aria-hidden="true" class="w-3 h-3 rounded-sm border border-black/10 flex-shrink-0" style="background:{{ $item['color'] }}"></span>
                            {{ $item['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Mobile backdrop --}}
        <div id="sidebarOverlay"
            class="fixed inset-0 bg-black/20 backdrop-blur-[1px] z-[9998] opacity-0 pointer-events-none transition-opacity duration-300"
            onclick="closeSidebar()">
        </div>

        {{-- Sidebar --}}
        <div id="sidebar" style="transform: translateX(100%);"
            class="fixed top-0 right-0 h-screen w-full sm:w-[480px] bg-white shadow-geist z-[9999] transition-transform duration-300 ease-in-out flex flex-col">

            {{-- Sidebar Header --}}
            <div class="flex-shrink-0 px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-md bg-gray-900 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-sm text-gray-900 leading-tight">Detail Konflik</h2>
                        <p class="text-[11px] text-gray-400">Klik marker untuk melihat detail</p>
                    </div>
                </div>
                <button onclick="closeSidebar()"
                    class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-gray-100 text-gray-400 hover:text-gray-900 transition cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            {{-- Sidebar Body --}}
            <div id="sidebarContent" aria-live="polite" aria-busy="false" class="flex-1 overflow-y-auto">
                <div class="flex flex-col items-center justify-center h-full text-center px-8 pb-16">
                    <div class="w-16 h-16 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Pilih titik di peta</p>
                    <p class="text-xs text-gray-400 mt-1">Detail konflik akan muncul di sini</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Mobile --}}
    <div class="sm:hidden">
        <div class="h-screen w-full flex flex-col items-center justify-center px-8 text-center">
            <a href="#" class="text-xl font-semibold tracking-tight text-gray-900">
                Jagakampung<span class="font-mono text-gray-400">.id</span>
            </a>
            <p class="mt-3 text-sm text-gray-500">Peta interaktif belum tersedia di perangkat seluler.</p>
            <p class="mt-1 font-mono text-[10px] uppercase tracking-widest text-gray-400">Buka di desktop</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/map.js') }}?v={{ filemtime(public_path('js/map.js')) }}"></script>
@endpush
