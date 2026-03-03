<div class="min-h-screen bg-gray-50 py-8 px-4 max-w-3xl mx-auto mt-2 rounded">

    <div x-data="{ open: @entangle('deleter') }">
        @include('partials.deleterModal')
    </div>

    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">

            <h1 class="text-2xl font-bold text-gray-900">Edit Data Konflik</h1>
        </div>

        <div class="space-y-6">

        {{-- SECTION 1: Lokasi --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-900 text-white text-xs font-bold shrink-0">1</div>
                <div>
                    <h2 class="font-semibold text-gray-900 text-sm">Lokasi Konflik</h2>
                    <p class="text-xs text-gray-500">Pilih wilayah dan tentukan titik koordinat di peta</p>
                </div>
            </div>
            <div class="p-6 space-y-5">

            <div class="w-full h-80 rounded-xl overflow-hidden border border-gray-200 z-10" id="map" wire:ignore></div>



            {{-- Region Dropdown --}}
            <div x-data="{ open: false }" @click.outside="open = false" @close-region.window="open = false" class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Administrasi Wilayah</label>
                <div
                    class="flex items-center justify-between w-full bg-gray-100 border border-gray-300 rounded-xl py-2.5 px-4 cursor-pointer hover:border-gray-400 transition-colors text-sm">
                    <span class="{{ $desa ? 'text-gray-900' : 'text-gray-400' }} truncate">{{ $region }}</span>
                    <svg class="h-4 w-4 text-gray-400 shrink-0 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div x-show="open" style="display: none;" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-2 border-b border-gray-100">
                        <input wire:model.live.debounce.150ms="chooseRegion" type="text"
                            class="w-full rounded-lg bg-gray-50 border border-gray-200 py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400"
                            placeholder="Cari nama desa..." />
                    </div>
                    <div class="max-h-52 overflow-y-auto">
                        @forelse ($administrasi as $value)
                            <a wire:click="selectRegion('{{ $value->name }}', '{{ $value->latitude }}', '{{ $value->longtitude }}', '{{ $value->geom }}')"
                                class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors">
                                @php
                                    $query = trim($chooseRegion);
                                    $name  = $value->name;
                                    $highlighted = $query !== ''
                                        ? preg_replace('/' . preg_quote($query, '/') . '/i', '<strong class="font-semibold text-gray-900">$0</strong>', $name)
                                        : $name;
                                @endphp
                                {!! $highlighted !!}
                            </a>
                        @empty
                            <div class="px-4 py-6 text-center text-sm text-gray-400">Ketik minimal 3 karakter untuk mencari</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Lat & Long --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Latitude</label>
                    <input disabled placeholder="Dipilih dari peta" type="text" wire:model="latitude"
                        class="w-full bg-gray-50 text-gray-500 border border-gray-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Longitude</label>
                    <input disabled placeholder="Dipilih dari peta" type="text" wire:model="longtitude"
                        class="w-full bg-gray-50 text-gray-500 border border-gray-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none" />
                </div>
            </div>

            </div>{{-- end p-6 section 1 --}}
        </div>{{-- end section 1 card --}}

        {{-- SECTION 2: Data Konflik --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-900 text-white text-xs font-bold shrink-0">2</div>
                <div>
                    <h2 class="font-semibold text-gray-900 text-sm">Data Konflik</h2>
                    <p class="text-xs text-gray-500">Informasi group, perusahaan, lembaga, dan status</p>
                </div>
            </div>
            <div class="p-6 space-y-5">

            {{-- Group & Perusahaan --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Group Perusahaan</label>
                    <select wire:model.live="selectedGroup"
                        class="w-full bg-white border border-gray-300 rounded-xl py-2.5 px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 cursor-pointer transition-colors">
                        <option value="">Pilih group</option>
                        @foreach($groups as $grp)
                            <option value="{{ $grp->nama }}">{{ $grp->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Perusahaan</label>
                    <select wire:model="selectedPerusahaan"
                        class="w-full bg-white border border-gray-300 rounded-xl py-2.5 px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 cursor-pointer transition-colors {{ !$selectedGroup ? 'opacity-50' : '' }}">
                        <option value="">{{ $selectedGroup ? 'Pilih perusahaan' : 'Pilih group dulu' }}</option>
                        @foreach($perusahaans as $perusahaan)
                            <option value="{{ $perusahaan->perusahaan }}">{{ $perusahaan->perusahaan }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Luas, KK & Status --}}
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Luas <span class="text-gray-400 font-normal">(ha)</span></label>
                    <input placeholder="0.00" type="number" wire:model="luas"
                        class="w-full bg-white border border-gray-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah KK</label>
                    <input placeholder="0" type="number" wire:model="kk"
                        class="w-full bg-white border border-gray-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select wire:model.live="selectedStatus"
                        class="w-full bg-white border border-gray-300 rounded-xl py-2.5 px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 cursor-pointer transition-colors">
                        <option value="">Pilih status</option>
                        <option value="aktif"> Aktif</option>
                        <option value="potensi"> Potensi</option>
                    </select>
                </div>
            </div>

            {{-- Lembaga --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Lembaga Terkait</label>
                <div x-data="{ open: @entangle('isPelaku') }">
                    <div @click="open = !open"
                        class="min-h-[44px] w-full bg-white border border-gray-300 rounded-xl py-2 px-4 cursor-pointer hover:border-gray-400 transition-colors flex flex-wrap gap-1.5 items-center">
                        @forelse ($lembagas as $key => $value)
                            <span class="inline-flex items-center gap-1 bg-gray-900 text-white text-xs rounded-full py-1 px-3">
                                {{ $value }}
                                <svg wire:click.stop="deleteTags({{ $key }})" class="h-3 w-3 cursor-pointer hover:text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        @empty
                            <span class="text-sm text-gray-400">Pilih lembaga terkait...</span>
                        @endforelse
                    </div>
                    <div x-show="open" style="display: none;" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        @click.outside="open = false"
                        class="relative z-20 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-700">Pilih Lembaga</span>
                            <svg @click="open = false" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 cursor-pointer hover:text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="max-h-48 overflow-y-auto">
                            @foreach ($listlembaga as $item)
                                <a class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors"
                                    wire:click="setLembaga('{{ $item->nama }}')">{{ $item->nama }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            </div>{{-- end p-6 section 2 --}}
        </div>{{-- end section 2 card --}}

        {{-- SECTION 3: Deskripsi --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-900 text-white text-xs font-bold shrink-0">3</div>
                <div>
                    <h2 class="font-semibold text-gray-900 text-sm">Deskripsi</h2>
                    <p class="text-xs text-gray-500">Penjelasan mengenai konflik dan perjuangan</p>
                </div>
            </div>
            <div class="p-6 space-y-5">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi Konflik</label>
                <textarea wire:model="deskripsikonflik" rows="4" placeholder="Jelaskan latar belakang dan kronologi konflik..."
                    class="w-full bg-white border border-gray-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors "></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi Perjuangan</label>
                <textarea wire:model="deskripsiperjuangan" rows="4" placeholder="Jelaskan upaya perjuangan yang telah dilakukan..."
                    class="w-full bg-white border border-gray-300 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors "></textarea>
            </div>

            </div>{{-- end p-6 section 3 --}}
        </div>{{-- end section 3 card --}}

        {{-- SECTION 4: Lampiran & Gambar --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-900 text-white text-xs font-bold shrink-0">4</div>
                <div>
                    <h2 class="font-semibold text-gray-900 text-sm">Lampiran & Dokumentasi</h2>
                    <p class="text-xs text-gray-500">Dokumen pendukung dan foto dokumentasi</p>
                </div>
            </div>
            <div class="p-6 space-y-6">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Dokumen Lampiran</label>
                    @include('partials.tableLampiran')
                </div>

                <div class="border-t border-gray-100"></div>

                {{-- Foto Dokumentasi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Foto Dokumentasi</label>

                    {{-- Gambar existing dari DB --}}
                    @if ($images)
                        <div class="mb-4">
                            <p class="text-xs text-gray-400 mb-2">Foto tersimpan</p>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach ($images as $index => $image)
                                    <div class="relative group rounded-xl overflow-hidden border border-gray-200" wire:key="old-img-{{ $index }}">
                                        <img src="{{ asset('storage/gambar/' . $image) }}" class="w-full h-28 object-cover">
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                                            <button type="button" wire:click="removeImage({{ $index }})"
                                                class="opacity-0 group-hover:opacity-100 transition-opacity bg-white rounded-full p-1.5 shadow">
                                                <svg class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="px-2 py-1 bg-white border-t border-gray-100">
                                            <p class="text-xs text-gray-500 truncate">{{ $image }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Upload gambar baru --}}
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 hover:border-gray-400 transition-colors">
                        <div class="flex flex-col items-center gap-1">
                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm text-gray-500">Tambah foto baru</span>
                            <span class="text-xs text-gray-400">PNG, JPG, WEBP — bisa multiple</span>
                        </div>
                        <input type="file" wire:model="newImages" multiple accept="image/*" class="hidden">
                    </label>

                    <div wire:loading wire:target="newImages" class="flex items-center gap-2 mt-3 text-sm text-gray-500">
                        <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Mengupload gambar...
                    </div>

                    @if ($newImages)
                        <div class="grid grid-cols-3 gap-3 mt-4">
                            @foreach ($newImages as $index => $image)
                                <div class="relative group rounded-xl overflow-hidden border border-gray-200" wire:key="new-img-{{ $index }}">
                                    <img src="{{ $image->temporaryUrl() }}" class="w-full h-28 object-cover">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                                        <button type="button" wire:click="removeNewImage({{ $index }})"
                                            class="opacity-0 group-hover:opacity-100 transition-opacity bg-white rounded-full p-1.5 shadow">
                                            <svg class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
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

            </div>{{-- end p-6 section 4 --}}
        </div>{{-- end section 4 card --}}

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between pt-2 pb-12">
            <a href="/cms/konflik" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Batal</a>
            <button wire:click="storeDatabase"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-700 text-white text-sm font-medium py-2.5 px-8 rounded-xl transition-colors cursor-pointer disabled:opacity-60">
                <span wire:loading.remove wire:target="storeDatabase">Simpan Perubahan</span>
                <span wire:loading wire:target="storeDatabase" class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Menyimpan...
                </span>
            </button>
        </div>

        </div>{{-- end space-y-6 --}}
    </div>{{-- end max-w-4xl --}}

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

        function renderMap(lat, lng, geom) {
            if (marker) { map.removeLayer(marker); marker = null; }
            if (geojsonLayer) { map.removeLayer(geojsonLayer); geojsonLayer = null; }

            currentGeom = geom;
            marker = L.marker([lat, lng]).addTo(map);

            if (geom) {
                geojsonLayer = L.geoJSON(typeof geom === 'string' ? JSON.parse(geom) : geom, {
                    style: () => ({ color: 'black', fillOpacity: 0.1, weight: 2 })
                }).addTo(map);
                map.fitBounds(geojsonLayer.getBounds());
            } else {
                map.setView([lat, lng], 14);
            }
        }

        // Klik map untuk pindah marker (hanya dalam polygon)
        map.on('click', function (e) {
            if (!currentGeom) return;

            if (isInsidePolygon(e.latlng)) {
                if (marker) map.removeLayer(marker);
                marker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
                currentLat = e.latlng.lat;
                currentLng = e.latlng.lng;
                @this.set('latitude', e.latlng.lat);
                @this.set('longtitude', e.latlng.lng);
            }
        });

        // Initial load
        if (currentLat && currentLng) {
            renderMap(currentLat, currentLng, @json($geom ?? null));
            currentGeom = @json($geom ?? null);
        }

        // Update saat selectRegion
        Livewire.hook('commit', ({ component, succeed }) => {
            succeed(() => {
                var { latitude, longtitude, geom } = component.canonical;
                var newLat = parseFloat(latitude);
                var newLng = parseFloat(longtitude);

                if (newLat && newLng && (newLat !== currentLat || newLng !== currentLng)) {
                    currentLat = newLat;
                    currentLng = newLng;
                    renderMap(newLat, newLng, geom);
                }
            });
        });

    });
</script>
@endpush

</div>
