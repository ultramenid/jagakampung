<div class="max-w-3xl px-4 mx-auto py-8">

    @include('partials.deleterModal')

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">PBPH</h1>
            <p class="text-sm text-gray-500 mt-0.5">Informasi tambahan konsesi PBPH</p>
        </div>
        <a href="/cms/tambah-pbph" class="gk-btn-primary gk-btn-sm">
            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            Tambah
        </a>
    </div>

    <div class="mb-4">
        <input wire:model.live="search" type="search" placeholder="Cari perusahaan atau kode PBPH…" class="gk-input sm:w-72">
    </div>

    <div class="gk-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-4 py-2.5 font-mono text-[10px] font-medium uppercase tracking-wider text-gray-400">
                        <button wire:click="sortingField('nama_perusahaan')" type="button" class="uppercase hover:text-gray-900 transition-colors">Perusahaan</button>
                    </th>
                    <th class="text-left px-4 py-2.5 font-mono text-[10px] font-medium uppercase tracking-wider text-gray-400">
                        <button wire:click="sortingField('kode_pbph')" type="button" class="uppercase hover:text-gray-900 transition-colors">Kode PBPH</button>
                    </th>
                    <th class="text-right px-4 py-2.5 font-mono text-[10px] font-medium uppercase tracking-wider text-gray-400">
                        <button wire:click="sortingField('luas')" type="button" class="uppercase hover:text-gray-900 transition-colors">Luas (ha)</button>
                    </th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($databases as $pbph)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $pbph->nama_perusahaan ?? '—' }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $pbph->kode_pbph }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-gray-500">
                        {{ $pbph->luas === null ? '—' : number_format($pbph->luas, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="/cms/editpbph/{{ $pbph->id }}" class="gk-btn-secondary gk-btn-sm">Edit</a>
                            @if ((int) session('role_id') === 0)
                                <button wire:click="delete({{ $pbph->id }})" type="button" class="gk-btn-danger gk-btn-sm">Hapus</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-12 text-center text-sm text-gray-500">Belum ada info PBPH</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($databases->hasPages())
        <div class="mt-4">
            {{ $databases->links() }}
        </div>
    @endif
</div>
