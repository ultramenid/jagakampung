@extends('layouts.dashboardLayout')

@section('content')
<div class="bg-white">
    @include('partials.header')
    @include('partials.nav')
</div>

@php
    $statusMeta = [
        'aktif'   => ['label' => 'Aktif',   'dot' => '#890620', 'text' => 'text-[#890620]', 'bg' => 'bg-[#890620]/8'],
        'potensi' => ['label' => 'Potensi', 'dot' => '#348AA7', 'text' => 'text-[#348AA7]', 'bg' => 'bg-[#348AA7]/8'],
        'draft'   => ['label' => 'Draft',   'dot' => '#605B51', 'text' => 'text-[#605B51]', 'bg' => 'bg-[#605B51]/8'],
    ];
    $fmt = fn ($n) => number_format($n, 0, ',', '.');
@endphp

<div class="max-w-3xl px-4 mx-auto py-10">

    {{-- Hero: the tally is the thesis --}}
    <div class="mb-10">
        <p class="font-mono text-[11px] uppercase tracking-widest text-gray-400 mb-3">Konflik Agraria Terpantau</p>
        <div class="flex items-end gap-4">
            <span class="font-mono text-6xl font-semibold text-gray-900 tabular-nums leading-none">
                {{ $fmt($stats['konflik']) }}
            </span>
            <span class="text-sm text-gray-500 pb-1.5">kasus tercatat</span>
        </div>
        <div class="mt-5 flex flex-wrap items-center gap-x-8 gap-y-2 text-sm text-gray-600">
            <span><span class="gk-mono font-semibold text-gray-900">{{ $fmt($stats['luas']) }}</span> ha lahan terdampak</span>
            <span><span class="gk-mono font-semibold text-gray-900">{{ $fmt($stats['kk']) }}</span> kepala keluarga</span>
        </div>
    </div>

    {{-- Status breakdown --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-10">
        @foreach ($statusMeta as $key => $meta)
            <div class="gk-card p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full" style="background: {{ $meta['dot'] }}"></span>
                    <span class="font-mono text-[10px] uppercase tracking-wider {{ $meta['text'] }}">{{ $meta['label'] }}</span>
                </div>
                <span class="gk-mono text-3xl font-semibold text-gray-900">{{ $fmt($byStatus[$key] ?? 0) }}</span>
            </div>
        @endforeach
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

    {{-- Recent cases --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-900">Kasus Terbaru</h2>
            <a href="{{ url('/cms/konflik') }}" class="text-xs text-accent-500 hover:text-accent-600 font-medium">Lihat peta →</a>
        </div>
        <div class="gk-card divide-y divide-gray-100">
            @forelse ($recent as $row)
                @php $m = $statusMeta[$row->status] ?? ['label' => $row->status, 'text' => 'text-gray-500', 'bg' => 'bg-gray-100', 'dot' => '#a3a3a3']; @endphp
                <a href="{{ url('/cms/edit-konflik/' . $row->id) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                    <span class="gk-mono text-xs text-gray-300 w-8 shrink-0">#{{ $row->id }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $row->desa }}, {{ $row->kabkota }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $row->provinsi }}</p>
                    </div>
                    <span class="gk-badge {{ $m['bg'] }} {{ $m['text'] }} shrink-0">{{ $m['label'] }}</span>
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
@endsection
