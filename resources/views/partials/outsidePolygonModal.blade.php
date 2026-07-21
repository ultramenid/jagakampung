{{--
    Outside-polygon resolution dialog (blocking).
    Fires on the client-side `open-outside-polygon` window event when the marker
    lands outside the selected administrasi polygon. The marker must end up inside,
    so this dialog has no escape hatch: no backdrop click, no ESC, no "leave outside".
    Two paths: auto-snap inside (turf.pointOnFeature) or manual lat/long entry.
    Pure Alpine — no Livewire round-trip needed for the interaction.
--}}
<div x-data="{
        open: false,
        manualMode: false,
        manualLat: '',
        manualLng: '',
        error: '',
        get liveStatus() {
            if (!window.gkKonflikMap) return '';
            var lat = parseFloat(this.manualLat);
            var lng = parseFloat(this.manualLng);
            if (isNaN(lat) || isNaN(lng)) return '';
            if (window.gkKonflikMap.isInside && window.gkKonflikMap.isInside(lat, lng)) return 'inside';
            return 'outside';
        },
        reset() { this.manualMode = false; this.manualLat = ''; this.manualLng = ''; this.error = ''; },
        snap() {
            if (window.gkKonflikMap && window.gkKonflikMap.snapInside) {
                window.gkKonflikMap.snapInside();
            }
            this.open = false;
            this.reset();
        },
        saveManual() {
            this.error = '';
            if (!window.gkKonflikMap || !window.gkKonflikMap.setManual) {
                this.error = 'Peta belum siap. Coba pilih wilayah lagi.';
                return;
            }
            var result = window.gkKonflikMap.setManual(this.manualLat, this.manualLng);
            if (result.ok) {
                this.open = false;
                this.reset();
            } else if (result.reason === 'outside') {
                this.error = 'Koordinat masih di luar batas wilayah.';
            } else {
                this.error = 'Masukkan nilai latitude dan longitude yang valid.';
            }
        }
    }"
    @open-outside-polygon.window="open = true; reset()"
    x-cloak>

    <template x-if="open">
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            {{-- blocking: no backdrop click handler, no ESC handler --}}
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" aria-hidden="true"></div>

            <div role="dialog" aria-modal="true" aria-labelledby="outsidePolygonTitle"
                 class="relative bg-white rounded-xl shadow-geist w-full max-w-md z-10 overflow-hidden"
                 x-init="$nextTick(() => { $refs.outsideFirst && $refs.outsideFirst.focus() })">

                {{-- Amber hairline warning strip --}}
                <div class="flex items-center gap-2 px-5 py-3 border-b border-amber-200 bg-amber-50">
                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-700">Titik di luar wilayah</span>
                </div>

                {{-- Choice mode --}}
                <div x-show="!manualMode" class="px-6 py-6">
                    <div class="flex items-start gap-5">
                        {{-- Signature: breach diagram — polygon outline with marker dot outside --}}
                        <div class="shrink-0 w-20 h-20 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center">
                            <svg viewBox="0 0 80 80" class="w-16 h-16" fill="none" stroke="currentColor">
                                <polygon points="14,28 38,12 64,22 58,56 24,52" class="text-gray-400" stroke-width="1.5" stroke-dasharray="3 2" fill="rgba(0,0,0,0.03)"/>
                                <circle cx="70" cy="64" r="3.5" class="text-amber-600" fill="currentColor" stroke="white" stroke-width="1"/>
                                <line x1="58" y1="56" x2="70" y2="64" class="text-amber-400" stroke-width="1" stroke-dasharray="1.5 1.5"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1 pt-1">
                            <h3 id="outsidePolygonTitle" class="text-sm font-semibold text-gray-900 leading-tight">Marker berada di luar batas administrasi</h3>
                            <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Titik konflik harus berada di dalam wilayah yang dipilih. Pindahkan otomatis ke titik terdekat di dalam batas, atau masukkan koordinat manual.</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 mt-6">
                        <button x-ref="outsideFirst" @click="snap()" type="button" class="gk-btn-primary gk-btn-sm w-full justify-center">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            Pindahkan otomatis ke dalam wilayah
                        </button>
                        <button @click="manualMode = true; error = ''; $nextTick(() => $refs.manualLatInput && $refs.manualLatInput.focus())" type="button" class="gk-btn-secondary gk-btn-sm w-full justify-center">
                            Input koordinat manual
                        </button>
                    </div>
                </div>

                {{-- Manual input mode --}}
                <div x-show="manualMode" style="display: none;" x-cloak class="px-6 py-6">
                    <div class="flex items-center gap-2 mb-3">
                        <button @click="manualMode = false; error = ''" type="button" class="text-gray-400 hover:text-gray-900 transition-colors -ml-1 p-1" aria-label="Kembali">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <h3 class="text-sm font-semibold text-gray-900">Input koordinat manual</h3>
                    </div>
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">Masukkan titik yang berada di dalam batas wilayah administrasi.</p>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="gk-label">Latitude</label>
                            <input x-ref="manualLatInput" x-model="manualLat" type="number" step="any" placeholder="-1.234567" class="gk-input gk-mono" @keydown.enter="saveManual()" />
                        </div>
                        <div>
                            <label class="gk-label">Longitude</label>
                            <input x-model="manualLng" type="number" step="any" placeholder="120.987654" class="gk-input gk-mono" @keydown.enter="saveManual()" />
                        </div>
                    </div>

                    {{-- Live inside/outside status pip — updates as the user types --}}
                    <div x-show="liveStatus" x-cloak class="flex items-center gap-1.5 mt-3">
                        <template x-if="liveStatus === 'inside'">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-green-700 bg-green-50 border border-green-200 rounded-full px-2.5 py-1">
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Di dalam wilayah
                            </span>
                        </template>
                        <template x-if="liveStatus === 'outside'">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-2.5 py-1">
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                Di luar wilayah
                            </span>
                        </template>
                    </div>

                    <p x-show="error" x-text="error" x-cloak class="text-xs text-red-600 mt-3"></p>

                    <div class="flex gap-3 mt-6">
                        <button @click="manualMode = false; error = ''" type="button" class="gk-btn-secondary gk-btn-sm flex-1">Kembali</button>
                        <button @click="saveManual()" type="button" class="gk-btn-primary gk-btn-sm flex-1">Simpan koordinat</button>
                    </div>
                </div>

            </div>
        </div>
    </template>
</div>