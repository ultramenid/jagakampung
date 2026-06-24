<div class="max-w-3xl px-4 mx-auto py-8">
    <div class="flex items-center gap-2 mb-6 text-xs">
        <a href="/cms/users" class="text-gray-400 hover:text-gray-900 transition-colors">Pengguna</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-medium">Edit Pengguna</span>
    </div>

    <div class="gk-card p-6 max-w-xl">
        <h1 class="text-base font-semibold text-gray-900 mb-6">Edit Pengguna</h1>

        <form wire:submit.prevent="storeDatabase">
            <div class="space-y-4">
                <div>
                    <label class="gk-label">Nama Lengkap</label>
                    <input wire:model="name" type="text" class="gk-input" placeholder="Masukkan nama lengkap">
                </div>
                <div>
                    <label class="gk-label">Email</label>
                    <input wire:model="email" type="email" class="gk-input @error('email') border-red-300 @enderror" placeholder="nama@contoh.com">
                    @error('email') <p class="gk-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="gk-label">Instansi</label>
                    <input wire:model="instansi" type="text" class="gk-input" placeholder="Masukkan nama instansi">
                </div>
                <div>
                    <label class="gk-label">Password <span class="text-gray-400 font-normal normal-case">(kosongkan jika tidak diubah)</span></label>
                    <input wire:model="password" type="password" class="gk-input" placeholder="••••••••">
                </div>
                <div>
                    <label class="gk-label">Role</label>
                    <select wire:model="role" class="gk-select">
                        <option value="0">Administrator</option>
                        <option value="1">User</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <a href="/cms/users" class="gk-btn-secondary gk-btn-sm">Batal</a>
                <button wire:loading.remove type="submit" class="gk-btn-primary gk-btn-sm">Simpan perubahan</button>
                <button wire:loading type="button" class="gk-btn-primary gk-btn-sm" disabled>
                    <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menyimpan…
                </button>
            </div>
        </form>
    </div>
</div>
