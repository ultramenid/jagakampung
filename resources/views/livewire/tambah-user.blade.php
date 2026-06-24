<div class="max-w-3xl px-4 mx-auto py-8">
    <div class="flex items-center gap-3 mb-6">
        <a href="/cms/users" class="text-xs text-gray-400 hover:text-gray-700 transition">Pengguna</a>
        <span class="text-gray-200">/</span>
        <span class="text-xs text-gray-600 font-medium">Tambah Pengguna</span>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 max-w-xl">
        <h1 class="text-sm font-semibold text-gray-900 mb-6">Tambah Pengguna</h1>

        <form wire:submit.prevent="storeDatabase">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Nama Lengkap</label>
                    <input wire:model="nama" type="text"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 @error('nama') border-red-300 @enderror"
                           placeholder="Masukkan nama lengkap">
                    @error('nama') <p class="mt-1 text-[10px] text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Email</label>
                    <input wire:model="email" type="email"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 @error('email') border-red-300 @enderror"
                           placeholder="nama@contoh.com">
                    @error('email') <p class="mt-1 text-[10px] text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Instansi</label>
                    <input wire:model="instansi" type="text"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10"
                           placeholder="Masukkan nama instansi">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Password</label>
                    <input wire:model="password" type="password"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 @error('password') border-red-300 @enderror"
                           placeholder="••••••••">
                    @error('password') <p class="mt-1 text-[10px] text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Role</label>
                    <select wire:model="role"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 bg-white">
                        <option value="">-- Pilih Role --</option>
                        <option value="0">Administrator</option>
                        <option value="1">User</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <a href="/cms/users"
                   class="px-4 py-2.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Batal
                </a>
                <button wire:loading.remove type="submit"
                        class="px-6 py-2.5 text-xs font-medium text-white bg-gray-900 hover:bg-gray-700 rounded-lg transition cursor-pointer">
                    Simpan
                </button>
                <button wire:loading type="button"
                        class="px-6 py-2.5 text-xs font-medium text-white bg-gray-700 rounded-lg cursor-not-allowed flex items-center gap-2">
                    <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menyimpan...
                </button>
            </div>
        </form>
    </div>
</div>
