<div class="max-w-3xl px-4 mx-auto py-8">
    <div class="flex items-center gap-2 mb-6 text-xs">
        <a href="/cms/konflik" class="text-gray-400 hover:text-gray-900 transition-colors">Konflik</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-medium">Tambah Artikel</span>
    </div>

    <div class="gk-card p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-base font-semibold text-gray-900">Tambah Artikel</h1>
            <span class="gk-badge bg-gray-100 text-gray-500">Konflik #{{ $konflik_id }}</span>
        </div>

        <form wire:submit.prevent="storeDatabase">
            <div class="space-y-5">
                <input type="hidden" wire:model="konflik_id">

                <div>
                    <label class="gk-label">Judul Artikel</label>
                    <input wire:model="judul_id" type="text" class="gk-input @error('judul_id') border-red-300 @enderror" placeholder="Masukkan judul artikel">
                    @error('judul_id') <p class="gk-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="gk-label">Deskripsi Artikel</label>
                    <textarea wire:model="deskripsi_id" rows="6" class="gk-input h-auto py-2.5 resize-none" placeholder="Isi artikel bahasa Indonesia"></textarea>
                </div>

                <div>
                    <label class="gk-label">Gambar</label>
                    <input wire:model="gambar" type="file" class="w-full text-sm text-gray-600 border border-gray-200 rounded-md px-3 py-2.5 file:mr-3 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-600 hover:file:bg-gray-200">
                    @error('gambar') <p class="gk-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="gk-label">Sumber</label>
                    <input wire:model="sumber" type="text" class="gk-input" placeholder="Contoh: Kompas.com">
                </div>

                <div>
                    <label class="gk-label">Tanggal Publish</label>
                    <input wire:model="tanggal_publish" type="date" class="gk-input">
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <a href="/cms/konflik" class="gk-btn-secondary gk-btn-sm">Batal</a>
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
