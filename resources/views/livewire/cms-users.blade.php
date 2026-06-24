<div class="max-w-3xl px-4 mx-auto py-8">

    @include('partials.deleterModal')

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Pengguna</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola akun pengguna sistem</p>
        </div>
        <a href="/cms/tambah-user" class="gk-btn-primary gk-btn-sm">
            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            Tambah
        </a>
    </div>

    <div class="mb-4">
        <input wire:model.live="search" type="search" placeholder="Cari pengguna…" class="gk-input sm:w-64">
    </div>

    <div class="gk-card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-4 py-2.5 font-mono text-[10px] font-medium uppercase tracking-wider text-gray-400">Nama</th>
                    <th class="text-left px-4 py-2.5 font-mono text-[10px] font-medium uppercase tracking-wider text-gray-400">Email</th>
                    <th class="text-left px-4 py-2.5 font-mono text-[10px] font-medium uppercase tracking-wider text-gray-400">Role</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($databases as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @if ($user->role == 0)
                            <span class="gk-badge bg-gray-900 text-white">Admin</span>
                        @else
                            <span class="gk-badge bg-gray-100 text-gray-600">User</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="/cms/edituser/{{ $user->id }}" class="gk-btn-secondary gk-btn-sm">Edit</a>
                            <button wire:click="delete({{ $user->id }})" type="button" class="gk-btn-danger gk-btn-sm">Hapus</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-12 text-center text-sm text-gray-500">Belum ada data pengguna</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
