<div class="max-w-3xl px-4 mx-auto py-8">
    <div class="flex items-center gap-2 mb-6 text-xs">
        <a href="/cms/perusahaan" class="text-gray-400 hover:text-gray-900 transition-colors">Perusahaan</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-medium">Tambah Perusahaan</span>
    </div>

    <div class="gk-card p-6 max-w-xl">
        <h1 class="text-base font-semibold text-gray-900 mb-6">Tambah Perusahaan</h1>

        <form wire:submit.prevent="storeDatabase">
            <div class="space-y-4">
                <div>
                    <label class="gk-label">Group</label>
                    <select wire:model="group" class="gk-select">
                        <option value="">— Pilih group —</option>
                        @foreach ($groups as $grp)
                            <option value="{{ $grp->nama }}">{{ $grp->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="gk-label">Nama Perusahaan</label>
                    <input wire:model="perusahaan" type="text" class="gk-input @error('perusahaan') border-red-300 @enderror" placeholder="Masukkan nama perusahaan">
                    @error('perusahaan') <p class="gk-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="gk-label">Deskripsi</label>
                    <textarea wire:model="deskripsi" rows="4" class="gk-input h-auto py-2.5 resize-none" placeholder="Deskripsi perusahaan (opsional)"></textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <a href="/cms/perusahaan" class="gk-btn-secondary gk-btn-sm">Batal</a>
                <button wire:loading.remove type="submit" class="gk-btn-primary gk-btn-sm">Simpan</button>
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
