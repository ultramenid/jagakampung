<div class="max-w-3xl px-4 mx-auto py-8">

    @include('partials.deleterModal')

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Perusahaan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola data perusahaan</p>
        </div>
        <a href="/cms/tambah-perusahaan" class="gk-btn-primary gk-btn-sm">
            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            Tambah
        </a>
    </div>

    <div class="mb-4">
        <input wire:model.live="search" type="search" placeholder="Cari perusahaan…" class="gk-input sm:w-64">
    </div>

    <div class="gk-card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-4 py-2.5 font-mono text-[10px] font-medium uppercase tracking-wider text-gray-400">Perusahaan</th>
                    <th class="text-left px-4 py-2.5 font-mono text-[10px] font-medium uppercase tracking-wider text-gray-400">Group</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($databases as $perusahaan)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $perusahaan->perusahaan }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $perusahaan->group ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="/cms/editperusahaan/{{ $perusahaan->id }}" class="gk-btn-secondary gk-btn-sm">Edit</a>
                            @if ((int) session('role_id') === 0)
                                <button wire:click="delete({{ $perusahaan->id }})" type="button" class="gk-btn-danger gk-btn-sm">Hapus</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-12 text-center text-sm text-gray-500">Belum ada data perusahaan</td>
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
