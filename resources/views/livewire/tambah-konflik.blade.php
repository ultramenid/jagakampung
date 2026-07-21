<div class="min-h-screen bg-gray-50 py-8 px-4 max-w-3xl mx-auto mt-2">

    <div x-data="{ open: @entangle('deleter') }">
        @include('partials.deleterModal')
    </div>

    @include('partials.outsidePolygonModal')

    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <p class="font-mono text-[11px] uppercase tracking-widest text-gray-400 mb-2">Entri Data</p>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Tambah Data Konflik</h1>
        </div>

        <div class="space-y-6">

            {{-- SECTION 1: Lokasi --}}
            {{-- ponytail: overflow-hidden removed so the absolute region dropdown can escape the card; header carries its own top rounding instead --}}
            <div class="gk-card">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-md">
                    <div class="flex items-center justify-center w-7 h-7 rounded-md bg-gray-900 text-white font-mono text-xs shrink-0">1</div>
                    <div>
                        <h2 class="font-semibold text-gray-900 text-sm">Lokasi Konflik</h2>
                        <p class="text-xs text-gray-500">Pilih wilayah dan tentukan titik koordinat di peta</p>
                    </div>
                </div>
                <div class="p-6 space-y-5">

                    <div class="w-full h-80 rounded-md overflow-hidden border border-gray-200 z-10" id="map" wire:ignore></div>

                    {{-- Region Dropdown --}}
                    <div x-data="{
                        open: false,
                        isLoadingMore: false,
                        async handleScroll(el) {
                            if (this.isLoadingMore) return;
                            if (el.scrollHeight - el.scrollTop <= el.clientHeight + 30) {
                                this.isLoadingMore = true;
                                await $wire.call('loadMoreResults');
                                this.isLoadingMore = false;
                            }
                        }
                    }" @click.outside="open = false" @close-region.window="open = false"
                        class="relative z-[500]">
                        <label class="gk-label">Administrasi Wilayah</label>
                        <div @click="open = !open; if(open) $nextTick(() => $refs.regionInput.focus())"
                            class="flex items-center justify-between w-full bg-white border border-gray-200 rounded-md h-10 px-3 cursor-pointer hover:border-gray-300 transition-colors text-sm">
                            <span class="{{ $desa ? 'text-gray-900' : 'text-gray-400' }} truncate">{{ $region }}</span>
                            <svg class="h-4 w-4 text-gray-400 shrink-0 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div x-show="open" style="display: none;" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            class="absolute z-[500] w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-geist overflow-hidden">
                            <div class="p-2 border-b border-gray-100">
                                <div class="relative">
                                    <input x-ref="regionInput" id="tk-region" wire:model.live.debounce.200ms="chooseRegion" type="text"
                                            class="gk-input h-9 pr-9" placeholder="Cari nama desa…" />
                                    <div wire:loading wire:target="chooseRegion" class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="max-h-40 overflow-y-auto" @scroll="handleScroll($el)">
                                @forelse ($administrasi as $value)
                                    <a wire:click="selectRegion('{{ $value->name }}', '{{ $value->latitude }}', '{{ $value->longtitude }}', '{{ $value->geom }}')"
                                        class="flex items-center px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors">
                                        @php
                                            $query = trim($chooseRegion);
                                            $name = $value->name;
                                            $highlighted = $query !== ''
                                                ? preg_replace('/' . preg_quote($query, '/') . '/i', '<strong class="font-semibold text-gray-900">$0</strong>', $name)
                                                : $name;
                                        @endphp
                                        {!! $highlighted !!}
                                    </a>
                                @empty
                                    <div class="px-4 py-6 text-center text-sm text-gray-400">Ketik minimal 3 karakter untuk mencari</div>
                                @endforelse
                                @if ($searchHasMore)
                                    <div wire:loading.class.remove="hidden" wire:target="loadMoreResults"
                                        class="hidden flex items-center justify-center gap-2 py-3 text-sm text-gray-500">
                                        <svg class="animate-spin h-4 w-4 shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span>Memuat lebih banyak…</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Lat & Long --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="tk-latitude" class="gk-label">Latitude</label>
                            <input id="tk-latitude" placeholder="Dipilih dari peta" disabled type="text" wire:model="latitude" class="gk-input gk-mono bg-gray-50 text-gray-500" />
                        </div>
                        <div>
                            <label for="tk-longitude" class="gk-label">Longitude</label>
                            <input id="tk-longitude" placeholder="Dipilih dari peta" disabled type="text" wire:model="longtitude" class="gk-input gk-mono bg-gray-50 text-gray-500" />
                        </div>
                    </div>

                </div>
            </div>

            {{-- SECTION 2: Data Konflik --}}
            <div class="gk-card overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center justify-center w-7 h-7 rounded-md bg-gray-900 text-white font-mono text-xs shrink-0">2</div>
                    <div>
                        <h2 class="font-semibold text-gray-900 text-sm">Data Konflik</h2>
                        <p class="text-xs text-gray-500">Informasi group, perusahaan, lembaga, dan status</p>
                    </div>
                </div>
                <div class="p-6 space-y-5">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="tk-group" class="gk-label">Group Perusahaan</label>
                            <select id="tk-group" wire:model.live="selectedGroup" class="gk-select">
                                <option value="">Pilih group</option>
                                @foreach ($groups as $grp)
                                    <option value="{{ $grp->nama }}">{{ $grp->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="tk-perusahaan" class="gk-label">Perusahaan</label>
                            <div x-data="{ open: false, search: '', items: @js($perusahaans->pluck('perusahaan')->toArray()) }" wire:key="perusahaan-{{ $selectedGroup }}" @click.outside="open = false; search = ''" class="relative">
                                <button type="button" @click="open = !open; search = '';"
                                    class="flex items-center justify-between w-full bg-white border border-gray-200 rounded-md h-10 px-3 cursor-pointer hover:border-gray-300 transition-colors text-sm {{ !$selectedGroup ? 'opacity-50' : '' }}">
                                    <span class="{{ $selectedPerusahaan ? 'text-gray-900' : 'text-gray-400' }} truncate">{{ $selectedPerusahaan ?: 'Cari perusahaan…' }}</span>
                                    <svg class="h-4 w-4 text-gray-400 shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                    class="absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-geist overflow-hidden">
                                    <div class="p-2 border-b border-gray-100">
                                        <input x-model.debounce.200ms="search" type="text" class="gk-input h-9" placeholder="Cari perusahaan…" @keydown.escape="open = false; search = ''" />
                                    </div>
                                    <div class="max-h-52 overflow-y-auto">
                                        <template x-for="item in items.filter(i => i.toLowerCase().includes(search.toLowerCase()))" :key="item">
                                            <div @click="$wire.set('selectedPerusahaan', item); open = false; search = '';"
                                                class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors" x-text="item">
                                            </div>
                                        </template>
                                        <div x-show="items.length === 0" class="px-4 py-6 text-center text-sm text-gray-400">{{ $selectedGroup ? 'Tidak ada perusahaan' : 'Pilih group dulu' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label for="tk-luas" class="gk-label">Luas <span class="text-gray-400 font-normal normal-case">(ha)</span></label>
                            <input id="tk-luas" placeholder="0.00" type="number" wire:model="luas" class="gk-input gk-mono" />
                        </div>
                        <div>
                            <label for="tk-kk" class="gk-label">Jumlah KK <span class="text-gray-400 font-normal">(opsional)</span></label>
                            <input id="tk-kk" placeholder="0" type="number" wire:model="kk" class="gk-input gk-mono" />
                        </div>
                        <div>
                            <label for="tk-jiwa" class="gk-label">Jumlah Jiwa Berkonflik</label>
                            <input id="tk-jiwa" placeholder="0" type="number" wire:model="jiwa" class="gk-input gk-mono" />
                        </div>
                        <div>
                            <label for="tk-status" class="gk-label">Status</label>
                            @if (session('role_id') === 1)
                                <select id="tk-status" disabled class="gk-select bg-gray-100 text-gray-500">
                                    <option value="draft">Draft</option>
                                </select>
                            @else
                                <select id="tk-status" wire:model.live="selectedStatus" class="gk-select">
                                    <option value="">Pilih status</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="potensi">Potensi</option>
                                    <option value="draft">Draft</option>
                                </select>
                            @endif
                        </div>
                    </div>

                    {{-- Lembaga --}}
                    <div>
                        <label class="gk-label">Lembaga Terkait</label>
                        <div x-data="{ open: @entangle('isPelaku') }">
                            <div @click="open = !open"
                                class="min-h-[40px] w-full bg-white border border-gray-200 rounded-md py-1.5 px-3 cursor-pointer hover:border-gray-300 transition-colors flex flex-wrap gap-1.5 items-center">
                                @forelse ($lembagas as $key => $value)
                                    <span class="inline-flex items-center gap-1 bg-gray-900 text-white text-xs rounded-full py-1 px-2.5">
                                        {{ $value }}
                                        <svg wire:click.stop="deleteTags({{ $key }})" class="h-3 w-3 cursor-pointer hover:text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-400">Pilih lembaga terkait…</span>
                                @endforelse
                            </div>
                            <div x-show="open" style="display: none;" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" @click.outside="open = false"
                                class="relative z-20 mt-1 bg-white border border-gray-200 rounded-xl shadow-geist overflow-hidden">
                                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                    <span class="text-sm font-medium text-gray-700">Pilih Lembaga</span>
                                    <svg @click="open = false" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 cursor-pointer hover:text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="max-h-48 overflow-y-auto">
                                    @foreach ($listlembaga as $item)
                                        <a class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors" wire:click="setLembaga('{{ $item->nama }}')">{{ $item->nama }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- SECTION 3: Deskripsi --}}
            <div class="gk-card overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center justify-center w-7 h-7 rounded-md bg-gray-900 text-white font-mono text-xs shrink-0">3</div>
                    <div>
                        <h2 class="font-semibold text-gray-900 text-sm">Deskripsi</h2>
                        <p class="text-xs text-gray-500">Penjelasan mengenai konflik dan perjuangan</p>
                    </div>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label for="tk-deskripsi-konflik" class="gk-label">Deskripsi Konflik</label>
                        <textarea id="tk-deskripsi-konflik" wire:model="deskripsikonflik" rows="6" placeholder="Jelaskan latar belakang dan kronologi konflik…" class="gk-input h-auto py-2.5 resize-none"></textarea>
                    </div>
                    <div>
                        <label for="tk-deskripsi-perjuangan" class="gk-label">Deskripsi Perjuangan</label>
                        <textarea id="tk-deskripsi-perjuangan" wire:model="deskripsiperjuangan" rows="6" placeholder="Jelaskan upaya perjuangan yang telah dilakukan…" class="gk-input h-auto py-2.5 resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- SECTION 4: Lampiran & Gambar --}}
            <div class="gk-card overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center justify-center w-7 h-7 rounded-md bg-gray-900 text-white font-mono text-xs shrink-0">4</div>
                    <div>
                        <h2 class="font-semibold text-gray-900 text-sm">Lampiran & Dokumentasi</h2>
                        <p class="text-xs text-gray-500">Dokumen pendukung dan foto dokumentasi</p>
                    </div>
                </div>
                <div class="p-6 space-y-6">

                    <div>
                        <label class="gk-label mb-3">Dokumen Lampiran</label>
                        @include('partials.tableLampiran')
                    </div>

                    <div class="border-t border-gray-100"></div>

                    <div>
                        <label class="gk-label mb-3">Foto Dokumentasi</label>

                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-md cursor-pointer bg-gray-50 hover:bg-gray-100 hover:border-gray-400 transition-colors">
                            <div class="flex flex-col items-center gap-1">
                                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm text-gray-500">Klik untuk upload gambar</span>
                                <span class="text-xs text-gray-400">PNG, JPG, WEBP — bisa multiple</span>
                            </div>
                            <input type="file" wire:model="images" multiple accept="image/*" class="hidden">
                        </label>

                        <div wire:loading.flex wire:target="images" class="flex items-center gap-2 mt-3 text-sm text-gray-500">
                            <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Mengupload gambar…
                        </div>

                        @if ($images)
                            <div class="grid grid-cols-3 gap-3 mt-4">
                                @foreach ($images as $index => $image)
                                    <div class="relative group rounded-md overflow-hidden border border-gray-200" wire:key="img-{{ $index }}">
                                        <img src="{{ $image->temporaryUrl() }}" class="w-full h-28 object-cover">
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                                            <button type="button" wire:click="removeImage({{ $index }})" class="opacity-0 group-hover:opacity-100 transition-opacity bg-white rounded-full p-1.5 shadow">
                                                <svg class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="px-2 py-1 bg-white border-t border-gray-100">
                                            <p class="text-xs text-gray-500 truncate">{{ $image->getClientOriginalName() }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-between pt-2 pb-12">
                <a href="/cms/konflik" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Batal</a>
                <button wire:click="storeDatabase" wire:loading.attr="disabled" class="gk-btn-primary px-8">
                    <span wire:loading.remove wire:target="storeDatabase">Simpan Data</span>
                    {{-- ponytail: .inline-flex modifier so Livewire sets display:inline-flex instead of its default inline-block,
                        which was clobbering the class and breaking the spinner/text centering --}}
                    <span wire:loading.inline-flex wire:target="storeDatabase" class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Menyimpan…
                    </span>
                </button>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", () => {

                var map = L.map('map', {
                    gestureHandling: true,
                    minZoom: 4
                }).setView([-1.0893, 120.9213], 4);

                L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    attribution: 'Auriga | Jagakampung',
                    minZoom: 4,
                }).addTo(map);

                var marker = null;
                var geojsonLayer = null;
                var currentGeom = null;
                var currentLat = {{ $latitude ?? 'null' }};
                var currentLng = {{ $longtitude ?? 'null' }};

                function isInsidePolygon(latlng) {
                    if (!currentGeom) return false;
                    var point = turf.point([latlng.lng, latlng.lat]);
                    var polygon = typeof currentGeom === 'string' ? JSON.parse(currentGeom) : currentGeom;
                    return turf.booleanPointInPolygon(point, polygon);
                }

                function setMarker(lat, lng) {
                    if (marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng]).addTo(map);
                    currentLat = lat;
                    currentLng = lng;

                    // sync ke Livewire
                    @this.set('latitude', lat);
                    @this.set('longtitude', lng);
                }

                function renderMap(lat, lng, geom) {
                    if (marker) map.removeLayer(marker);
                    if (geojsonLayer) map.removeLayer(geojsonLayer);

                    currentGeom = geom;
                    marker = L.marker([lat, lng]).addTo(map);

                    if (geom) {
                        geojsonLayer = L.geoJSON(typeof geom === 'string' ? JSON.parse(geom) : geom, {
                            style: () => ({
                                color: 'black',
                                fillOpacity: 0.1,
                                weight: 2
                            })
                        }).addTo(map);
                        map.fitBounds(geojsonLayer.getBounds());
                    } else {
                        map.setView([lat, lng], 14);
                    }

                    // ponytail: warn when the marker lands outside the selected administrasi polygon —
                    // a concave polygon's stored centroid can sit outside its own boundary
                    if (currentGeom && !isInsidePolygon(marker.getLatLng())) {
                        // defer so Alpine has registered its window listener on init
                        setTimeout(() => window.dispatchEvent(new Event('open-outside-polygon')), 100);
                    }
                }

                // Klik pada map
                map.on('click', function(e) {
                    if (!currentGeom) return;

                    if (isInsidePolygon(e.latlng)) {
                        setMarker(e.latlng.lat, e.latlng.lng);
                    } else {
                        // optional: kasih feedback kalau klik di luar polygon
                        console.warn('Klik di luar polygon');
                    }
                });

                // ponytail: expose helpers for the outside-polygon dialog (snap inside / manual entry / live status)
                window.gkKonflikMap = {
                    snapInside() {
                        if (!currentGeom || !marker) return;
                        var poly = typeof currentGeom === 'string' ? JSON.parse(currentGeom) : currentGeom;
                        // turf.pointOnFeature always returns a point inside a Polygon/MultiPolygon —
                        // guarantees a valid in-bounds snap even for concave shapes
                        var inside = turf.pointOnFeature(poly);
                        setMarker(inside.geometry.coordinates[1], inside.geometry.coordinates[0]);
                    },
                    setManual(lat, lng) {
                        var nLat = parseFloat(lat);
                        var nLng = parseFloat(lng);
                        if (isNaN(nLat) || isNaN(nLng)) {
                            return { ok: false, reason: 'invalid' };
                        }
                        if (!isInsidePolygon(L.latLng(nLat, nLng))) {
                            return { ok: false, reason: 'outside' };
                        }
                        setMarker(nLat, nLng);
                        return { ok: true };
                    },
                    isInside(lat, lng) {
                        if (!currentGeom) return false;
                        return isInsidePolygon(L.latLng(lat, lng));
                    }
                };

                // Initial load
                if (currentLat && currentLng) {
                    renderMap(currentLat, currentLng, @json($geom ?? null));
                    currentGeom = @json($geom ?? null);
                }

                // Update saat selectRegion
                Livewire.hook('commit', ({
                    component,
                    succeed
                }) => {
                    succeed(() => {
                        var {
                            latitude,
                            longtitude,
                            geom
                        } = component.canonical;
                        var lat = parseFloat(latitude);
                        var lng = parseFloat(longtitude);

                        if (lat && lng && (lat !== currentLat || lng !== currentLng)) {
                            currentLat = lat;
                            currentLng = lng;
                            renderMap(lat, lng, geom);
                        }
                    });
                });

            });
        </script>
    @endpush

</div>
