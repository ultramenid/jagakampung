@extends('layouts.dashboardLayout')

@section('content')
<div class="bg-white shadow-sm">
    @include('partials.header')
    @include('partials.nav')
</div>

@php
    $statusMeta = [
        'aktif'   => ['label' => 'Aktif',   'dot' => 'bg-status-aktif',   'text' => 'text-status-aktif',   'bar' => 'bg-status-aktif'],
        'potensi' => ['label' => 'Potensi', 'dot' => 'bg-status-potensi', 'text' => 'text-status-potensi', 'bar' => 'bg-status-potensi'],
        'draft'   => ['label' => 'Draft',   'dot' => 'bg-status-draft',   'text' => 'text-status-draft',   'bar' => 'bg-status-draft'],
    ];
    $fmt = fn ($n) => number_format($n, 0, '.', ',');
    $caseTotal = max(1, array_sum(array_map(fn ($k) => (int) ($byStatus[$k] ?? 0), array_keys($statusMeta))));
@endphp

<div class="max-w-3xl px-4 mx-auto py-10">

    {{-- Hero: the tally is the thesis --}}
    <div class="mb-8">
        <p class="font-mono text-[11px] uppercase tracking-widest text-gray-400 mb-3">Konflik Terpantau</p>
        <div class="flex items-end gap-4">
            <span class="font-mono text-6xl font-semibold text-gray-900 tabular-nums leading-none">
                {{ $fmt($stats['konflik']) }}
            </span>
            <span class="text-sm text-gray-500 pb-1.5">konflik tercatat</span>
        </div>
        <div class="mt-5 flex flex-wrap items-center gap-x-8 gap-y-2 text-sm text-gray-600">
            <span><span class="gk-mono font-semibold text-gray-900">{{ number_format(round($stats['luas']), 0, '.', ',') }}</span> ha lahan terdampak</span>
            <span><span class="gk-mono font-semibold text-gray-900">{{ $fmt($stats['kk']) }}</span> kepala keluarga</span>
        </div>
    </div>

    {{-- Caseload composition — one ledger bar, segments sized by status --}}
    <div class="gk-card p-4 mb-10">
        <p class="font-mono text-[10px] uppercase tracking-widest text-gray-400 mb-3">Komposisi konflik</p>
        <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
            @foreach ($statusMeta as $key => $meta)
                @php $w = round(($byStatus[$key] ?? 0) / $caseTotal * 100, 2); @endphp
                @if ($w > 0)
                    <div class="{{ $meta['bar'] }} h-full" style="width: {{ $w }}%" title="{{ $meta['label'] }}"></div>
                @endif
            @endforeach
        </div>
        <div class="mt-4 flex flex-wrap gap-x-8 gap-y-3">
            @foreach ($statusMeta as $key => $meta)
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $meta['dot'] }}"></span>
                    <span class="font-mono text-[10px] uppercase tracking-wider {{ $meta['text'] }}">{{ $meta['label'] }}</span>
                    <span class="gk-mono text-sm font-semibold text-gray-900">{{ $fmt($byStatus[$key] ?? 0) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Registry counts --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-gray-200 rounded-md overflow-hidden border border-gray-200 mb-10">
        @foreach ([
            ['Perusahaan', $stats['perusahaan'], '/cms/perusahaan'],
            ['Lembaga', $stats['instansi'], '/cms/instansi'],
            ['Group', $stats['grup'], '/cms/group'],
            ['Pengguna', $stats['users'], '/cms/users'],
        ] as [$label, $value, $href])
            <a href="{{ url($href) }}" class="bg-white p-4 hover:bg-gray-50 transition-colors">
                <p class="font-mono text-[10px] uppercase tracking-wider text-gray-400 mb-1">{{ $label }}</p>
                <span class="gk-mono text-2xl font-semibold text-gray-900">{{ $fmt($value) }}</span>
            </a>
        @endforeach
    </div>

    {{-- Recent cases — the ledger --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-900">konflik Terbaru</h2>
            <a href="{{ url('/cms/konflik') }}" class="text-xs text-accent-500 hover:text-accent-600 font-medium">Lihat peta →</a>
        </div>
        <div class="gk-card overflow-hidden">
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 font-mono text-[10px] uppercase tracking-widest text-gray-400">
                <span>Kode · Lokasi</span><span>Status</span>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($recent as $row)
                    @php $m = $statusMeta[$row->status] ?? ['label' => $row->status, 'text' => 'text-gray-500', 'dot' => 'bg-gray-400']; @endphp
                    <a href="{{ url('/cms/edit-konflik/' . $row->id) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                        <span class="gk-mono text-xs text-gray-300 w-10 shrink-0">#{{ $row->id }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $row->desa }}, {{ $row->kabkota }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $row->provinsi }}</p>
                        </div>
                        <span class="flex items-center gap-2 shrink-0 font-mono text-[10px] uppercase tracking-wider {{ $m['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $m['dot'] }}"></span>{{ $m['label'] }}
                        </span>
                    </a>
                @empty
                    <div class="px-4 py-12 text-center">
                        <p class="text-sm text-gray-500">Belum ada data konflik.</p>
                        <a href="{{ url('/cms/tambah-konflik') }}" class="gk-btn-primary gk-btn-sm mt-3 inline-flex">Tambah konflik</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Draft articles --}}
    <div class="mt-8">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-900">Artikel Perlu Publis</h2>
            <span class="font-mono text-[10px] text-gray-400 tabular-nums">{{ count($draftArtikel) }} draft</span>
        </div>
        <div class="gk-card overflow-hidden">
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 font-mono text-[10px] uppercase tracking-widest text-gray-400">
                <span>Judul · Lokasi</span><span>Tanggal</span>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($draftArtikel as $a)
                    <a href="{{ url('/cms/edit-artikel/' . $a->id) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $a->judul_id }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $a->desa }}, {{ $a->kabkota }}</p>
                        </div>
                        <span class="font-mono text-[11px] text-gray-400 shrink-0">{{ $a->created_at ? \Carbon\Carbon::parse($a->created_at)->format('d/m/Y') : '-' }}</span>
                    </a>
                @empty
                    <div class="px-4 py-12 text-center">
                        <p class="text-sm text-gray-500">Semua artikel sudah terpublish.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
