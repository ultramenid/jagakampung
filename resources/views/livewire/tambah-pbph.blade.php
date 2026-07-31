<div class="max-w-3xl px-4 mx-auto py-8">
    <div class="flex items-center gap-2 mb-6 text-xs">
        <a href="/cms/pbph" class="text-gray-400 hover:text-gray-900 transition-colors">PBPH</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-medium">Tambah Info PBPH</span>
    </div>

    <div class="gk-card p-6 max-w-xl">
        <h1 class="text-base font-semibold text-gray-900 mb-6">Tambah Info PBPH</h1>

        <form wire:submit.prevent="storeDatabase">
            <div class="space-y-4">
                {{-- Dropdown konsesi dengan pencarian, pola sama dengan pemilih desa di form konflik --}}
                <div x-data="{ open: false }" @click.outside="open = false" @close-konsesi.window="open = false"
                     class="relative z-[500]">
                    <label class="gk-label">Konsesi PBPH <span class="text-[#b8324a]" aria-hidden="true">*</span></label>
                    <div @click="open = !open; if(open) $nextTick(() => $refs.konsesiInput.focus())"
                         class="flex items-center justify-between w-full bg-white border border-gray-200 rounded-md h-10 px-3 cursor-pointer hover:border-gray-300 transition-colors text-sm">
                        <span class="{{ $kode_pbph ? 'text-gray-900' : 'text-gray-400' }} truncate">{{ $namaKonsesi }}</span>
                        <svg class="h-4 w-4 text-gray-400 shrink-0 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div x-show="open" style="display: none;" x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         class="absolute z-[500] w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-geist overflow-hidden">
                        <div class="p-2 border-b border-gray-100">
                            <div class="relative">
                                <input x-ref="konsesiInput" wire:model.live.debounce.200ms="cariKonsesi" type="text"
                                       class="gk-input h-9 pr-9" placeholder="Cari nama perusahaan atau kode…" />
                                <div wire:loading wire:target="cariKonsesi" class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="max-h-52 overflow-y-auto">
                            @forelse ($konsesis as $konsesi)
                                <a wire:click="selectKonsesi('{{ $konsesi['kode_pbph'] }}')"
                                   class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors">
                                    @php
                                        $query = trim($cariKonsesi);
                                        $nama = $konsesi['namobj'];
                                        $highlighted = $query !== ''
                                            ? preg_replace('/' . preg_quote($query, '/') . '/i', '<strong class="font-semibold text-gray-900">$0</strong>', $nama)
                                            : $nama;
                                    @endphp
                                    {!! $highlighted !!}
                                    <span class="font-mono text-[10px] text-gray-400 block mt-0.5">{{ $konsesi['kode_pbph'] }}</span>
                                </a>
                            @empty
                                <div class="px-4 py-6 text-center text-sm text-gray-400">
                                    @if (trim($cariKonsesi) !== '')
                                        Konsesi tidak ditemukan
                                    @else
                                        Daftar konsesi tidak bisa dimuat dari GeoServer, atau semua konsesi sudah punya info.
                                    @endif
                                </div>
                            @endforelse
                        </div>
                        @if ($konsesis->count() === 50)
                            <div class="px-4 py-2 border-t border-gray-100 text-[11px] text-gray-400">Menampilkan 50 teratas — persempit pencarian.</div>
                        @endif
                    </div>
                </div>

                @include('partials.pbphFields')
            </div>

            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <a href="/cms/pbph" class="gk-btn-secondary gk-btn-sm">Batal</a>
                <button wire:loading.remove type="submit" class="gk-btn-primary gk-btn-sm">Simpan</button>
                <button wire:loading.inline-flex type="button" class="gk-btn-primary gk-btn-sm" disabled>
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
