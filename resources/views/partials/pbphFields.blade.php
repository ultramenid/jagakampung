{{-- Field yang sama persis dipakai form tambah & edit — satu berkas, dua pemakai. --}}
<div>
    <label class="gk-label">Izin Pertama <span class="text-[#b8324a]" aria-hidden="true">*</span></label>
    <textarea wire:model="izin_pertama" rows="2" class="gk-input h-auto py-2.5 resize-none" placeholder="Nomor & tanggal SK izin pertama"></textarea>
</div>
<div>
    <label class="gk-label">Izin Saat Ini <span class="text-[#b8324a]" aria-hidden="true">*</span></label>
    <textarea wire:model="izin_saat_ini" rows="2" class="gk-input h-auto py-2.5 resize-none" placeholder="Nomor & tanggal SK izin yang berlaku"></textarea>
</div>
<div>
    <label class="gk-label">Luas (ha) <span class="text-[#b8324a]" aria-hidden="true">*</span></label>
    <input wire:model="luas" type="number" step="0.01" min="0" class="gk-input" placeholder="Luas konsesi dalam hektare">
</div>

<div class="pt-2 border-t border-gray-100">
    <p class="font-mono text-[10px] uppercase tracking-wider text-gray-400 mt-4 mb-3">Pemilik</p>
    <div class="space-y-4">
        <div>
            <label class="gk-label">Komisaris <span class="text-[#b8324a]" aria-hidden="true">*</span></label>
            <textarea wire:model="komisaris" rows="2" class="gk-input h-auto py-2.5 resize-none" placeholder="Satu nama per baris"></textarea>
        </div>
        <div>
            <label class="gk-label">Direktur Utama <span class="text-[#b8324a]" aria-hidden="true">*</span></label>
            <textarea wire:model="direktur_utama" rows="2" class="gk-input h-auto py-2.5 resize-none" placeholder="Satu nama per baris"></textarea>
        </div>
        <div>
            <label class="gk-label">Direktur <span class="text-[#b8324a]" aria-hidden="true">*</span></label>
            <textarea wire:model="direktur" rows="2" class="gk-input h-auto py-2.5 resize-none" placeholder="Satu nama per baris"></textarea>
        </div>
    </div>
</div>

<div class="pt-2 border-t border-gray-100">
    @php $extWarna = ['pdf' => 'text-red-600 bg-red-50', 'jpg' => 'text-blue-600 bg-blue-50', 'jpeg' => 'text-blue-600 bg-blue-50', 'png' => 'text-blue-600 bg-blue-50', 'webp' => 'text-blue-600 bg-blue-50']; @endphp
    <p class="font-mono text-[10px] uppercase tracking-wider text-gray-400 mt-4 mb-1">Lampiran</p>
    <p class="text-xs text-gray-500 mb-3">Opsional. PDF atau gambar, maks 10 MB per berkas. Nama boleh diubah.</p>

    @if (count($lampirans))
        <div class="space-y-2 mb-3">
            @foreach ($lampirans as $i => $item)
                @php
                    $namaBerkas = is_string($item['file']) ? $item['file'] : $item['file']->getClientOriginalName();
                    $ext = strtolower(pathinfo($namaBerkas, PATHINFO_EXTENSION));
                @endphp
                <div wire:key="lampiran-{{ $item['id'] ?? 'baru' }}-{{ $i }}"
                     class="flex items-center gap-2 rounded-md border border-gray-200 p-2">
                    <span class="font-mono text-[10px] font-medium uppercase px-1.5 py-0.5 rounded {{ $extWarna[$ext] ?? 'text-gray-600 bg-gray-100' }}">{{ $ext ?: 'file' }}</span>
                    <input wire:model="lampirans.{{ $i }}.nama" type="text" class="gk-input h-8 text-xs flex-1" placeholder="Nama lampiran">
                    @if (is_string($item['file']))
                        <a href="{{ Storage::url('pbph-lampiran/'.$item['file']) }}"
                           target="_blank" rel="noopener" class="text-[11px] text-gray-500 hover:text-gray-900 px-1">Unduh</a>
                    @else
                        <span class="text-[11px] text-gray-400 px-1">Baru</span>
                    @endif
                    <button wire:click="removeLampiran({{ $i }})" type="button"
                            class="p-1 rounded-md hover:bg-red-50 text-red-500" title="Hapus lampiran">
                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    <input wire:model="uploads" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.webp"
           class="w-full text-xs text-gray-600 border border-dashed border-gray-300 rounded-md px-3 py-2 file:mr-3 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-600 hover:file:bg-gray-200">
    <div wire:loading wire:target="uploads" class="text-[11px] text-gray-400 mt-1">Mengunggah…</div>
    @error('uploads.*') <p class="gk-error">{{ $message }}</p> @enderror
</div>

<div class="pt-2 border-t border-gray-100">
    <p class="font-mono text-[10px] uppercase tracking-wider text-gray-400 mt-4 mb-1">Konflik Terkait</p>
    <p class="text-xs text-gray-500 mb-3">Opsional. Saring per perusahaan, lalu centang konflik yang terkait.</p>

    <select wire:model.live="filterPerusahaan" class="gk-select mb-2">
        <option value="">Semua perusahaan</option>
        @foreach ($perusahaans as $namaPerusahaan)
            <option value="{{ $namaPerusahaan }}">{{ $namaPerusahaan }}</option>
        @endforeach
    </select>

    {{-- checkbox, bukan <select multiple>: mengganti filter tidak boleh diam-diam
         membuang pilihan yang sudah dicentang tapi tersaring keluar --}}
    <div class="border border-gray-200 rounded-md divide-y divide-gray-100 max-h-52 overflow-y-auto">
        @forelse ($konfliks as $konflik)
            <label class="flex items-start gap-3 px-3 py-2.5 hover:bg-gray-50 cursor-pointer transition-colors">
                <input type="checkbox" wire:model="konflikIds" value="{{ $konflik->id }}" class="mt-0.5 shrink-0">
                <span class="text-xs text-gray-700 leading-relaxed">
                    {{ $konflik->desa }}, {{ $konflik->kecamatan }} — {{ $konflik->kabkota }}, {{ $konflik->provinsi }}
                    <span class="block text-gray-500 mt-0.5">{{ $konflik->perusahaan ?: '—' }} · {{ $konflik->status }}</span>
                </span>
            </label>
        @empty
            <p class="px-3 py-6 text-center text-xs text-gray-400">
                {{ $filterPerusahaan !== '' ? 'Tidak ada konflik untuk perusahaan ini.' : 'Belum ada data konflik.' }}
            </p>
        @endforelse
    </div>
</div>
