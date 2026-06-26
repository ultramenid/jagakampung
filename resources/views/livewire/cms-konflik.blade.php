<div>

    {{-- Floating Add Button --}}
    <a href="{{ url('/cms/tambah-konflik') }}"
        class="fixed z-30 bottom-8 right-8 group flex items-center gap-2 bg-gray-900 hover:bg-gray-700 text-white text-sm font-medium pl-4 pr-5 py-3 rounded-full shadow-geist transition-colors duration-200 cursor-pointer select-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                clip-rule="evenodd" />
        </svg>
        Tambah Konflik
    </a>

    <div role="group" aria-label="Legenda status konflik"
        class="fixed z-20 bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-3 bg-white/90 backdrop-blur-sm border border-gray-200 rounded-full px-5 py-2.5 shadow-geist font-mono text-[11px] uppercase tracking-wider text-gray-600 select-none">

        <button id="toggleAktif" aria-pressed="true" aria-label="Tampilkan titik Aktif" class="legend-btn flex items-center gap-1.5 cursor-pointer">
            <span aria-hidden="true" class="w-3 h-3 rounded-full bg-status-aktif border-2 border-white shadow-sm inline-block"></span>
            Aktif
        </button>

        <span aria-hidden="true" class="w-px h-3 bg-gray-200"></span>

        <button id="togglePotensi" aria-pressed="true" aria-label="Tampilkan titik Potensi" class="legend-btn flex items-center gap-1.5 cursor-pointer">
            <span aria-hidden="true" class="w-3 h-3 rounded-full bg-white border-[3px] border-status-potensi shadow-sm inline-block"></span>
            Potensi
        </button>

        <span aria-hidden="true" class="w-px h-3 bg-gray-200"></span>

        <button id="toggleDraft" aria-pressed="true" aria-label="Tampilkan titik Draft" class="legend-btn flex items-center gap-1.5 cursor-pointer">
            <span aria-hidden="true" class="w-3 h-3 rounded-full bg-white border-[3px] border-status-draft shadow-sm inline-block"></span>
            Draft
        </button>

    </div>

    {{-- Map --}}
    <div role="application" aria-label="Peta konflik " class="w-full h-[89vh] z-10 rounded-md" id="map" wire:ignore></div>

    {{-- Mobile backdrop --}}
    <div id="sidebarOverlay"
        class="fixed inset-0 bg-black/20 backdrop-blur-[1px] z-30 opacity-0 pointer-events-none transition-opacity duration-300"
        onclick="closeSidebar()">
    </div>

    {{-- Sidebar --}}
    <div id="sidebar" style="transform: translateX(100%);"
        class="fixed top-0 right-0 h-screen w-full sm:w-[480px] bg-white shadow-geist z-40 transition-transform duration-300 ease-in-out flex flex-col">

        {{-- Sidebar Header --}}
        <div class="flex-shrink-0 px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-md bg-gray-900 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-sm text-gray-900 leading-tight">Detail Konflik</h2>
                    <p class="text-[11px] text-gray-400">Klik marker untuk melihat detail</p>
                </div>
            </div>
            <button onclick="closeSidebar()"
                class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-gray-100 text-gray-400 hover:text-gray-900 transition cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        {{-- Sidebar Body --}}
        <div id="sidebarContent" aria-live="polite" aria-busy="false" class="flex-1 overflow-y-auto">
            <div class="flex flex-col items-center justify-center h-full text-center px-8 pb-16">
                <div
                    class="w-16 h-16 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-300" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-700">Pilih titik di peta</p>
                <p class="text-xs text-gray-400 mt-1">Detail konflik akan muncul di sini</p>
            </div>
        </div>
    </div>



    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", () => {

                        var APP_URL = window.location.origin;
                        const CSRF = '{{ csrf_token() }}';
                        var pruneCluster = new PruneClusterForLeaflet();
                        var markersPotensi = [];
                        var markersAktif = [];
                        var markersDraft = [];

                        let isAktifVisible = true;
                        let isPotensiVisible = true;
                        let isDraftVisible = true;

                        const sidebar = document.getElementById('sidebar');
                        const sidebarContent = document.getElementById('sidebarContent');
                        const overlay = document.getElementById('sidebarOverlay');
                        const userRole = {{ session('role_id') ?? 0 }};
                        const userId = {{ session('id') ?? 0 }};

                        window.openSidebar = function() {
                            sidebar.style.transform = 'translateX(0)';
                            overlay.style.opacity = '1';
                            overlay.style.pointerEvents = 'auto';
                        };

                        window.closeSidebar = function() {
                            sidebar.style.transform = 'translateX(100%)';
                            overlay.style.opacity = '0';
                            overlay.style.pointerEvents = 'none';
                            setSelectedKonflik(null);
                        };

                        function setSelectedKonflik(id) {
                            const url = new URL(window.location.href);
                            if (id) {
                                url.searchParams.set('konflik', id);
                            } else {
                                url.searchParams.delete('konflik');
                            }
                            window.history.replaceState({}, '', url);
                        }

                        function getSelectedKonflik() {
                            const params = new URLSearchParams(window.location.search);
                            return params.get('konflik');
                        }

                        window.deleteKonflik = function(id) {
                            if (!confirm('Hapus data konflik ini? Tindakan ini tidak dapat dibatalkan.')) return;
                            setSelectedKonflik(null);
                            fetch(`${APP_URL}/cms/konflik/${id}/delete`, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                            })
                            .then(res => res.json().then(body => ({ ok: res.ok, body })))
                            .then(({ ok, body }) => {
                                if (!ok) throw new Error(body.message || 'Gagal menghapus data');
                                closeSidebar();
                                location.reload();
                            })
                            .catch(err => alert(err.message || 'Gagal menghapus data'));
                        };

                        // ── Map ───────────────────────────────────────────────────────────────
                        var map = L.map('map', {
                            gestureHandling: false,
                            minZoom: 5,
                            fadeAnimation: true,
                        }).setView([-1.0893, 120.9213], 4);

                        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                            attribution: 'Auriga | Jagakampung',
                            minZoom: 5,
                        }).addTo(map);

                        // ── Cluster icon ──────────────────────────────────────────────────────
                        pruneCluster.BuildLeafletClusterIcon = function(cluster) {
                            var aktif = cluster.stats[0] || 0;
                            var potensi = cluster.stats[1] || 0;
                            var draft = cluster.stats[2] || 0;
                            var total = aktif + potensi + draft;
                            var size = 38, r = size / 3;
                            var html;

                            if (aktif > 0 && potensi > 0 && draft > 0) {
                                html = `<div style="width:${size}px;height:${size}px;border-radius:50%;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.22);display:flex;flex-direction:column;position:relative;font:bold 11px system-ui,sans-serif;">
                                    <div style="flex:1;background:var(--color-status-aktif);"></div>
                                    <div style="flex:1;background:var(--color-status-potensi);"></div>
                                    <div style="flex:1;background:var(--color-status-draft);"></div>
                                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:white;text-shadow:0 1px 4px rgba(0,0,0,0.5);">${total}</div>
                                </div>`;
                            } else if (aktif > 0 && draft > 0) {
                                html = `<div style="width:${size}px;height:${size}px;border-radius:50%;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.22);display:flex;flex-direction:column;position:relative;font:bold 11px system-ui,sans-serif;">
                                    <div style="flex:1;background:var(--color-status-aktif);"></div>
                                    {{-- <div style="flex:1;background:#348AA7;"></div> --}}
                                    <div style="flex:1;background:var(--color-status-draft);"></div>
                                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:white;text-shadow:0 1px 4px rgba(0,0,0,0.5);">${total}</div>
                                </div>`;
                            }else if (aktif > 0 && potensi > 0) {
                                html = `<div style="width:${size}px;height:${size}px;border-radius:50%;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.22);display:flex;flex-direction:column;position:relative;font:bold 11px system-ui,sans-serif;">
                                    <div style="flex:1;background:var(--color-status-aktif);"></div>
                                    <div style="flex:1;background:var(--color-status-potensi);"></div>
                                    {{-- <div style="flex:1;background:#605B51;"></div> --}}
                                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:white;text-shadow:0 1px 4px rgba(0,0,0,0.5);">${total}</div>
                                </div>`;
                            } else {
                                html = `<div style="width:${size}px;height:${size}px;border-radius:50%;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.22);display:flex;flex-direction:column;position:relative;font:bold 11px system-ui,sans-serif;">
                                    {{-- <div style="flex:1;background:#890620;"></div> --}}
                                    <div style="flex:1;background:var(--color-status-potensi);"></div>
                                    <div style="flex:1;background:var(--color-status-draft);"></div>
                                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:white;text-shadow:0 1px 4px rgba(0,0,0,0.5);">${total}</div>
                                </div>`;
                            }
                            return L.divIcon({
                                className: '',
                                iconSize: [size, size],
                                iconAnchor: [r, r],
                                html
                            });
                        };
                        pruneCluster.Cluster.Size = 50;

                        // ── Marker icons ──────────────────────────────────────────────────────
                        const iconAktif = L.divIcon({
                            className: '',
                            iconSize: [20, 20],
                            iconAnchor: [10, 10],
                            popupAnchor: [0, -13],
                            html: `<div style="width:20px;height:20px;border-radius:50%;background:var(--color-status-aktif);border:2.5px solid white;box-shadow:0 1px 6px rgba(137,6,32,0.45);"></div>`
                        });
                        const iconPotensi = L.divIcon({
                            className: '',
                            iconSize: [20, 20],
                            iconAnchor: [10, 10],
                            popupAnchor: [0, -13],
                            html: `<div style="width:20px;height:20px;border-radius:50%;background:white;border:3px solid var(--color-status-potensi);box-shadow:0 1px 6px rgba(52,138,167,0.35);"></div>`
                        });
                        const iconDraft = L.divIcon({
                            className: '',
                            iconSize: [20, 20],
                            iconAnchor: [10, 10],
                            popupAnchor: [0, -13],
                            html: `<div style="width:20px;height:20px;border-radius:50%;background:white;border:3px solid var(--color-status-draft);box-shadow:0 1px 6px rgba(234, 179, 8, 0.35);"></div>`
                        });

                        // ── Loading skeleton ──────────────────────────────────────────────────
                        function showLoading() {
                            sidebarContent.innerHTML = `
                <div class="p-5 space-y-5 animate-pulse">
                    <div class="flex items-center justify-between">
                        <div class="h-6 w-20 bg-gray-100 rounded-full"></div>
                        <div class="h-7 w-16 bg-gray-100 rounded-lg"></div>
                    </div>
                    <div class="flex gap-4 border-b border-gray-100 pb-1">
                        <div class="h-3 w-16 bg-gray-100 rounded"></div>
                        <div class="h-3 w-16 bg-gray-100 rounded"></div>
                        <div class="h-3 w-16 bg-gray-100 rounded"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        ${[...Array(4)].map(() => `<div class="bg-gray-50 rounded-xl p-3 space-y-1.5"><div class="h-2.5 w-12 bg-gray-200 rounded"></div><div class="h-3.5 w-24 bg-gray-300 rounded"></div></div>`).join('')}
                    </div>
                    <div class="space-y-2">
                        <div class="h-2.5 w-24 bg-gray-100 rounded"></div>
                        <div class="h-3 w-full bg-gray-100 rounded"></div>
                        <div class="h-3 w-5/6 bg-gray-100 rounded"></div>
                        <div class="h-3 w-4/6 bg-gray-100 rounded"></div>
                    </div>
                </div>`;
                        }

                        // ── Render helpers ────────────────────────────────────────────────────
                        function renderMedia(data) {
                            const images = data?.data?.media?.gambar ?? [];
                            if (!images.length) {
                                return `<div class="flex flex-col items-center justify-center py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-200 mb-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs font-medium text-gray-400">Tidak ada foto dokumentasi</p>
                </div>`;
                            }
                            return `<div class="grid grid-cols-2 gap-2">
                ${images.map(img => `
                            <a href="/storage/gambar/${img}" class="glightbox group relative block overflow-hidden rounded-xl border border-gray-100">
                                <img src="/storage/gambar/${img}" class="w-full h-36 object-cover transition duration-300 group-hover:scale-105" alt="${img}">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition duration-300 flex items-center justify-center">
                                    <div class="w-8 h-8 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-700" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        `).join('')}
            </div>`;
                        }

                        function renderLampiran(data) {
                            const lampiran = data?.data?.media?.lampiran ?? [];
                            const extColors = {
                                PDF: {
                                    bg: '#fef2f2',
                                    text: '#dc2626'
                                },
                                DOC: {
                                    bg: '#eff6ff',
                                    text: '#2563eb'
                                },
                                DOCX: {
                                    bg: '#eff6ff',
                                    text: '#2563eb'
                                },
                                XLS: {
                                    bg: '#f0fdf4',
                                    text: '#16a34a'
                                },
                                XLSX: {
                                    bg: '#f0fdf4',
                                    text: '#16a34a'
                                },
                            };
                            if (!lampiran.length) {
                                return `<div class="flex flex-col items-center justify-center py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-200 mb-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs font-medium text-gray-400">Tidak ada lampiran</p>
                </div>`;
                            }
                            return `<div class="space-y-2">
                ${lampiran.map(item => {
                    const ext = (item.file.split('.').pop() || '').toUpperCase();
                    const c   = extColors[ext] ?? { bg: '#f3f4f6', text: '#374151' };
                    return `
<a href="/storage/lampiran/${item.file}" target="_blank"
class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-300 hover:bg-gray-50 transition group">

    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center text-[10px] font-bold"
         style="background:${c.bg};color:${c.text};">
        ${ext || 'FILE'}
    </div>

    <div class="flex-1 min-w-0">
        <div class="text-xs font-semibold text-gray-800 truncate">
            ${item.nama ?? item.file}
        </div>

        <div class="text-[10px] text-gray-400 truncate mt-0.5">
            ${item.file}
        </div>
    </div>

    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-4 h-4 text-gray-300 group-hover:text-gray-500 flex-shrink-0 transition"
         viewBox="0 0 20 20"
         fill="currentColor">

        <path fill-rule="evenodd"
              d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707..."
              clip-rule="evenodd"/>
    </svg>

</a>
`;
                }).join('')
        } </div>`;
            }

            // ── Main sidebar renderer ─────────────────────────────────────────────
            function renderSidebar(data) {
                const isAdmin = userRole === 0;
                const canManage = isAdmin || data.data.user_id == userId;
                const status = data.data.atribut.status;
                const colors = {
                    aktif: { dot: 'var(--color-status-aktif)', bg: '#fef2f2', text: 'var(--color-status-aktif)' },
                    potensi: { dot: 'var(--color-status-potensi)', bg: '#eff8fb', text: '#1d7a95' },
                    draft: { dot: 'var(--color-status-draft)', bg: '#f5f5f0', text: 'var(--color-status-draft)' },
                };
                const c = colors[status] ?? colors.draft;
                const totalLamp = data?.data?.media?.lampiran?.length ?? 0;
                const totalMedia = data?.data?.media?.gambar?.length ?? 0;
                const totalArtikel = data?.data?.artikel?.length ?? 0;
                const badgeStyle = `background:${c.bg};color:${c.text};`;
                const borderColor = c.dot;

                sidebarContent.innerHTML = `
                <div x-data="{ tab: 'general' }">

                    <div class="px-5 pt-4 pb-3 border-b border-gray-100 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span style="background:${c.bg};color:${c.text};"
                                      class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full flex-shrink-0">
                                    <span style="width:5px;height:5px;border-radius:50%;background:${c.dot};display:inline-block;flex-shrink:0;"></span>
                                    ${status}
                                </span>
                            </div>
                            <p class="text-[11px] text-gray-400 truncate">${data.data.lokasi.provinsi}, ${data.data.lokasi.kabkota}</p>
                        </div>
                        <div class="flex-shrink-0 flex items-center gap-2">
                            ${canManage ? `
                            <a href="${APP_URL}/cms/edit-konflik/${data.data.id}"
                               class="inline-flex items-center gap-1.5 text-[11px] font-medium border border-gray-200 hover:border-gray-700 hover:text-gray-900 text-gray-500 px-3 py-1.5 rounded-md transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                </svg>
                                Edit
                            </a>
                            ` : ''}
                            ${isAdmin ? `
                            <button type="button" onclick="deleteKonflik(${data.data.id})" data-id="${data.data.id}"
                               class="inline-flex items-center gap-1.5 text-[11px] font-medium border border-red-200 text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Hapus
                            </button>
                            ` : ''}
                        </div>
                    </div>

                    <div class="flex gap-0 border-b border-gray-100 bg-gray-50/60 px-5">
                        <button @click="tab = 'general'"
                                :class="tab === 'general' ? 'border-b-2 text-gray-900 font-semibold' : 'text-gray-400 hover:text-gray-600'"
                                style="border-color:${borderColor}"
                                class="py-3 px-1 mr-5 text-xs transition focus:outline-none cursor-pointer">
                            Informasi
                        </button>
                        <button @click="tab = 'lampiran'"
                                :class="tab === 'lampiran' ? 'border-b-2 text-gray-900 font-semibold' : 'text-gray-400 hover:text-gray-600'"
                                style="border-color:${borderColor}"
                                class="py-3 px-1 mr-5 text-xs transition focus:outline-none cursor-pointer flex items-center gap-1.5">
                            Lampiran
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="${badgeStyle}">${totalLamp}</span>
                        </button>
                        <button @click="tab = 'media'"
                                :class="tab === 'media' ? 'border-b-2 text-gray-900 font-semibold' : 'text-gray-400 hover:text-gray-600'"
                                style="border-color:${borderColor}"
                                class="py-3 px-1 mr-5 text-xs transition focus:outline-none cursor-pointer flex items-center gap-1.5">
                            Media
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="${badgeStyle}">${totalMedia}</span>
                        </button>
                        <button @click="tab = 'artikel'"
                                :class="tab === 'artikel' ? 'border-b-2 text-gray-900 font-semibold' : 'text-gray-400 hover:text-gray-600'"
                                style="border-color:${borderColor}"
                                class="py-3 px-1 text-xs transition focus:outline-none cursor-pointer">
                            Artikel
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="${badgeStyle}">${totalArtikel}</span>
                        </button>
                    </div>

                    <div x-show="tab === 'general'" class="px-5 py-4 space-y-5">

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Lokasi</p>
                            <div class="grid grid-cols-2 gap-2">
                                ${[
                                    ['Provinsi',     data.data.lokasi.provinsi],
                                    ['Kab / Kota',   data.data.lokasi.kabkota],
                                    ['Kecamatan',    data.data.lokasi.kecamatan],
                                    ['Desa',         data.data.lokasi.desa],
                                ].map(([label, val]) => `
                                            <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                                                <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">${label}</div>
                                                <div class="text-xs font-semibold text-gray-800 leading-snug">${val ?? '—'}</div>
                                            </div>`).join('')}
                            </div>
                            <div class="mt-2 flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-[10px] text-gray-500 font-mono">${data.data.lokasi.koordinat.lat}, ${data.data.lokasi.koordinat.lng}</span>
                            </div>
                        </div>

                        <div class="h-px bg-gray-100"></div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Atribut</p>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                                    <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Group</div>
                                    <div class="text-xs font-semibold text-gray-800">${data.data.atribut.group ?? '—'}</div>
                                </div>
                                <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                                    <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Perusahaan</div>
                                    <div class="text-xs font-semibold text-gray-800 leading-snug">${data.data.atribut.perusahaan ?? '—'}</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                                    <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Luas Ha</div>
                                    <div class="text-xs font-semibold text-gray-800">${data.data.atribut.luas != null ? Number(data.data.atribut.luas) : '—'}</div>
                                </div>
                                <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                                    <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">KK</div>
                                    <div class="text-xs font-semibold text-gray-800 leading-snug">${data.data.atribut.kk ?? '—'}</div>
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-gray-100"></div>

                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Deskripsi Konflik</p>
                                <p class="text-xs text-gray-700 whitespace-pre-line leading-relaxed">${data.data.deskripsi.konflik ?? '—'}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Deskripsi Perjuangan</p>
                                <p class="text-xs text-gray-700 whitespace-pre-line leading-relaxed">${data.data.deskripsi.perjuangan ?? '—'}</p>
                            </div>
                        </div>

                        <div class="h-px bg-gray-100"></div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Lembaga Pendamping</p>
                            <p class="text-xs text-gray-700 leading-relaxed">${data.data.lembaga ?? '—'}</p>
                        </div>

                    </div>

                    <div x-show="tab === 'lampiran'" class="px-5 py-4 pb-6">
                        ${renderLampiran(data)}
                    </div>

                    <div x-show="tab === 'media'" class="px-5 py-4 pb-6">
                        ${renderMedia(data)}
                    </div>

                    {{-- <div x-show="tab === 'artikel'" class="px-5 py-4 pb-6">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Artikel Terkait</p>
                            <a href="${APP_URL}/cms/tambah-artikel/${data.data.id}"
                               class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-500 border border-gray-200 hover:border-gray-700 hover:text-gray-900 px-2.5 py-1.5 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                                Tambah
                            </a>
                        </div>
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-xs font-medium text-gray-400">Belum ada artikel</p>
                            <p class="text-[10px] text-gray-300 mt-1">Klik Tambah untuk menambahkan artikel terkait</p>
                        </div>
                    </div> --}}

                    <div x-show="tab === 'artikel'" class="px-5 py-4 pb-6">

                        <div class="flex items-center justify-between mb-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                Artikel Terkait
                            </p>

                            <a href="${APP_URL}/cms/tambah-artikel/${data.data.id}"
                            class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-500 border border-gray-200 hover:border-gray-700 hover:text-gray-900 px-2.5 py-1.5 rounded-lg transition-colors">
                                Tambah
                            </a>
                        </div>

                        ${
                            data.data.artikel && data.data.artikel.length > 0
                            ? `
                                    <div class="space-y-4">
                                        ${data.data.artikel.map(item => `
                                    <a href="${APP_URL}/cms/edit-artikel/${item.id}"
                                    class="block border border-gray-100 rounded-xl overflow-hidden hover:border-gray-300 hover:bg-gray-50 transition">

                                        ${
                                            item.gambar
                                            ? `<img src="/storage/${item.gambar}"
                                                            class="w-full h-44 object-cover">`
                                            : `<div class="w-full h-44 bg-gray-100 flex items-center justify-center text-gray-300 text-xs">
                                                        Tidak ada gambar
                                                    </div>`
                                        }

                                        <div class="p-4 space-y-2">
                                            <p class="text-sm font-semibold text-gray-800 line-clamp-2 leading-snug">
                                                ${item.judul_id}
                                            </p>

                                            ${
                                                item.deskripsi_id
                                                ? `<p class="text-xs text-gray-500 line-clamp-3 leading-relaxed">
                                                        ${item.deskripsi_id}
                                                    </p>`
                                                : ''
                                            }

                                            <p class="text-[10px] text-gray-400 pt-1">
                                                ${item.tanggal_publish ?? '-'}
                                            </p>
                                        </div>

                                    </a>
                                `).join('')}
                                    </div>
                                    `
                            : `
                                    <div class="flex flex-col items-center justify-center py-10 text-center">
                                        <p class="text-xs font-medium text-gray-400">Belum ada artikel</p>
                                        <p class="text-[10px] text-gray-300 mt-1">
                                            Klik Tambah untuk menambahkan artikel terkait
                                        </p>
                                    </div>
                                    `
                        }

                    </div>

                </div>
            `;

                setTimeout(() => {
                    if (window.lightbox) window.lightbox.destroy();
                    window.lightbox = GLightbox({
                        selector: '.glightbox',
                        touchNavigation: true,
                        loop: false
                    });
                }, 0);
            }

            // ── Cluster marker setup ──────────────────────────────────────────────
            pruneCluster.PrepareLeafletMarker = function(marker, data, category) {
                marker.setIcon(data.icon);
                marker.bindTooltip(data.tooltip, {
                    direction: 'top',
                    sticky: true,
                    opacity: 0.9
                });
                marker.on('click', function() {
                    openSidebar();
                    showLoading();
                    setSelectedKonflik(data.idKasus);
                    fetch(`${APP_URL}/cms/rest-map/${data.idKasus}`)
                        .then(res => res.json())
                        .then(res => renderSidebar(res))
                        .catch(() => {
                            sidebarContent.innerHTML = `
                            <div class="flex flex-col items-center justify-center py-12 px-8 text-center">
                                <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600">Gagal memuat data</p>
                                <p class="text-xs text-gray-400 mt-1">Coba klik marker lagi</p>
                            </div>`;
                        });
                });
            };

            function registerMarker(feature, category, icon) {
                const marker = new PruneCluster.Marker(
                    feature.properties.lat,
                    feature.properties.long, {
                        idKasus: feature.properties.id,
                        status: feature.properties.status,
                        icon: icon,
                        tooltip: feature.properties.status,
                        userId: feature.properties.user_id,
                    }
                );


                marker.category = category;
                pruneCluster.RegisterMarker(marker);
                return marker;
            }

            function applyFilterStatus() {
                markersAktif.forEach(marker => {
                    if(marker) {
                        marker.filtered = !isAktifVisible;
                    }
                })

                markersPotensi.forEach(marker => {
                    if(marker) {
                        marker.filtered = !isPotensiVisible;
                    }
                })

                markersDraft.forEach(marker => {
                    if(marker) {
                        marker.filtered = !isDraftVisible;
                    }
                })
                pruneCluster.ProcessView();
            }

            // ── Restore from URL if available ─────────────────────────────────────
            const selectedKonflikId = getSelectedKonflik();

            // ── Load GeoJSON ──────────────────────────────────────────────────────
            $.ajax({
                type: 'GET',
                url: APP_URL + '/cms/rest-map',
                dataType: 'json',
                success: function(data) {
                    let selectedMarker = null;

                    L.geoJSON(data, {
                        onEachFeature: function(feature) {
                            if (userRole !== 0 && feature.properties.user_id != userId) return;
                            let marker;
                            if (feature.properties.status === 'aktif') {
                                marker = registerMarker(feature, 0, iconAktif);
                                markersAktif.push(marker);
                            } else if (feature.properties.status === 'potensi') {
                                marker = registerMarker(feature, 1, iconPotensi);
                                markersPotensi.push(marker);
                            } else if (feature.properties.status === 'draft') {
                                marker = registerMarker(feature, 2, iconDraft);
                                markersDraft.push(marker);
                            }

                            if (marker && String(feature.properties.id) === selectedKonflikId) {
                                selectedMarker = marker;
                            }
                        }
                    });
                    map.addLayer(pruneCluster);

                    if (selectedMarker) {
                        openSidebar();
                        showLoading();
                        fetch(`${APP_URL}/cms/rest-map/${selectedMarker.data.idKasus}`)
                            .then(res => res.json())
                            .then(res => renderSidebar(res))
                            .catch(() => {
                                sidebarContent.innerHTML = `
                            <div class="flex flex-col items-center justify-center py-12 px-8 text-center">
                                <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600">Gagal memuat data</p>
                                <p class="text-xs text-gray-400 mt-1">Coba klik marker lagi</p>
                            </div>`;
                            });
                    }
                },
            });

            document.getElementById('toggleAktif').addEventListener('click', function() {
                isAktifVisible = !isAktifVisible;
                document.getElementById('toggleAktif').setAttribute('aria-pressed', String(isAktifVisible));
                this.classList.toggle('opacity-40');
                applyFilterStatus();
            });

            document.getElementById('togglePotensi').addEventListener('click', function() {
                isPotensiVisible = !isPotensiVisible;
                document.getElementById('togglePotensi').setAttribute('aria-pressed', String(isPotensiVisible));
                this.classList.toggle('opacity-40');
                applyFilterStatus();
            });

            document.getElementById('toggleDraft').addEventListener('click', function() {
            isDraftVisible = !isDraftVisible;
            document.getElementById('toggleDraft').setAttribute('aria-pressed', String(isDraftVisible));
            this.classList.toggle('opacity-40');
            applyFilterStatus();
            });

            });
        </script>
    @endpush
</div>
