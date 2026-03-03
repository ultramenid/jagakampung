<div class="max-w-3xl px-4 mx-auto py-8">

    @include('partials.deleterModal')

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-sm font-semibold text-gray-900">Pengguna</h1>
            <p class="text-xs text-gray-400 mt-0.5">Kelola akun pengguna sistem</p>
        </div>
        <a href="/cms/tambah-user"
           class="inline-flex items-center gap-1.5 bg-gray-900 hover:bg-gray-700 text-white text-xs font-medium py-2 px-4 rounded-lg transition">
            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            Tambah
        </a>
    </div>

    <div class="mb-4">
        <input wire:model.live="search" type="search" placeholder="Cari pengguna…"
               class="w-full sm:w-64 border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-gray-900/10">
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Nama</th>
                    <th class="text-left px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Email</th>
                    <th class="text-left px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Role</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($databases as  $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @if ($user->role_id == 0)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-900 text-white">Admin</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600">User</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="/cms/edituser/{{ $user->id }}"
                               class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                Edit
                            </a>
                            <button wire:click="delete({{ $user->id }})" type="button"
                                    class="px-3 py-1.5 text-xs font-medium text-red-500 border border-red-100 rounded-lg hover:bg-red-50 transition cursor-pointer">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-xs text-gray-400">Belum ada data pengguna</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- <div class="mt-4">@include('livewire.pagination', ['paginator' => $users])</div> --}}
</div>
