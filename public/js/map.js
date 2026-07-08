// INIT MAP
const map = L.map("map", {
    center: [-0.7893, 109.9213],
    zoom: 6,
    minZoom: 6,
    zoomControl: false,
});

let markersAktif = [];
let markersPotensi = [];

// PRUNE CLUSTER
var pruneCluster = new PruneClusterForLeaflet();
var APP_URL = window.location.origin;

let isAktifVisible = true;
let isPotensiVisible = true;

// Map untuk menyimpan marker berdasarkan koordinat (lat,lng string) - untuk cluster lookup
const markersByCoord = {};

// Cache semua konflik untuk pencarian koordinat
let allKonflikData = [];

// Track apakah marker sudah diklik (untuk menghindari konflik dengan map click)
let markerClicked = false;

// SIDEBAR
const sidebar = document.getElementById("sidebar");
const sidebarContent = document.getElementById("sidebarContent");
const overlay = document.getElementById("sidebarOverlay");

window.openSidebar = function () {
    sidebar.style.transform = "translateX(0)";
    overlay.style.opacity = "1";
    overlay.style.pointerEvents = "auto";
};

window.closeSidebar = function () {
    sidebar.style.transform = "translateX(100%)";
    overlay.style.opacity = "0";
    overlay.style.pointerEvents = "none";
    updateSelectedKonflik(null);
};

function updateSelectedKonflik(id) {
    const url = new URL(window.location.href);
    if (id) {
        url.searchParams.set("konflik", id);
    } else {
        url.searchParams.delete("konflik");
    }
    window.history.replaceState({}, "", url);
}

function getSelectedKonflik() {
    return new URLSearchParams(window.location.search).get("konflik");
}

// Fly to a konflik from the left-rail list and load its detail
window.focusKonflik = function (id, lat, lng, skipClusterCheck) {
    // Cek apakah ada beberapa konflik di koordinat yang sama (dengan tolerance kecil)
    // Hanya cek jika skipClusterCheck = false (klik dari sidebar kiri)
    if (!skipClusterCheck) {
        const nearbyConflicts = [];
        const tolerance = 0.0001; // tolerance untuk perbedaan koordinat

        allKonflikData.forEach((f) => {
            const fLat = parseFloat(f.properties.lat);
            const fLng = parseFloat(f.properties.long);
            const status = (f.properties.status || "").toLowerCase();

            // Cek apakah koordinat sama (dengan tolerance)
            if (
                (status === "aktif" || status === "potensi") &&
                Math.abs(fLat - lat) < tolerance &&
                Math.abs(fLng - lng) < tolerance
            ) {
                nearbyConflicts.push({
                    id: f.properties.id,
                    status: f.properties.status,
                    lat: fLat,
                    lng: fLng,
                    desa: f.properties.desa,
                    kecamatan: f.properties.kecamatan,
                    kabkota: f.properties.kabkota,
                    provinsi: f.properties.provinsi,
                    luas: f.properties.luas,
                    kk: f.properties.kk,
                });
            }
        });

        // Jika ada lebih dari satu konflik di lokasi yang sama, zoom dulu ke titiknya
        // baru tampilkan pilihan (FIX: sebelumnya map.setView tidak pernah terpanggil
        // di kasus ini karena ada `return` sebelum baris map.setView di bawah)
        if (nearbyConflicts.length > 1) {
            map.setView([lat, lng], 18, { animate: true });
            setTimeout(() => {
                showClusterKonflikList(nearbyConflicts, lat, lng);
            }, 300);
            return;
        }
    }

    // Jika hanya satu konflik atau skipClusterCheck=true, langsung tampilkan detail
    // Zoom ke level 18 agar cluster "pecah" (level zoom tinggi menampilkan marker individu)
    map.setView([lat, lng], 18, { animate: true });

    setTimeout(() => {
        openSidebar();
        showLoading();
        updateSelectedKonflik(id);

        fetch(`/cms/rest-map/${id}?source=public`)
            .then((res) => {
                if (!res.ok) throw new Error("HTTP error " + res.status);
                return res.json();
            })
            .then((res) => {
                if (!res || !res.data) throw new Error("Data kosong");
                renderSidebar(res);
            })
            .catch((err) => {
                console.error("Fetch error:", err);
                sidebarContent.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12 px-8 text-center">
                        <p class="text-sm font-medium text-gray-600">Gagal memuat data</p>
                        <p class="text-xs text-gray-400 mt-1">ID: ${id}, ${err.message}</p>
                    </div>`;
            });
    }, 300);
};

// BASEMAP
const baseLayers = [
    {
        url: "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
        icon: "../assets/osm.png",
        addToMap: true,
    },
];

const baseLayerConfigs = baseLayers.map((b) => {
    const layer = L.tileLayer(b.url, {
        attribution: "Auriga Nusantara | Jagakampung",
        detectRetina: true,
        maxNativeZoom: 17,
    });

    if (b.addToMap) {
        layer.addTo(map);
    }

    return { layer, icon: b.icon, name: "" };
});

if (typeof L.bmSwitcher !== "undefined") {
    new L.bmSwitcher(baseLayerConfigs, { position: "bottomleft" }).addTo(map);
}

// WMS LAYERS
const wmsLayers = {
    deforestasi2023: "simontini:Auriga - Deforestasi 2023",
    deforestasi2024: "simontini:ID_STADI24_CEA",
    kawasanhutan: "simontini:Forest_estate_adm",
    hutanalam: "simontini:Hutan_Alam_adm",
    kantonghabitat: "simontini:KantongGajah2018RTM",
    hgu: "kpa:HGU_BPN_2019",
    // adminkabkota: 'administrative_boundaries',
    potensiminerba: "tiger:Geologi 100K Selected",
    pbph: "simontini:PBPH_AR_50K_DESEMBER_2023",
    iup: "simontini:mining_concession_2025_01_27",
};

const layers = {};

for (const [key, layerName] of Object.entries(wmsLayers)) {
    layers[key] = L.tileLayer.wms("https://aws.simontini.id/geoserver/wms", {
        layers: layerName,
        transparent: true,
        format: "image/png",
    });
}

// TOGGLE WMS
Object.keys(layers).forEach((key) => {
    const checkbox = document.getElementById(key);
    if (!checkbox) return;

    checkbox.addEventListener("change", function () {
        if (this.checked) {
            map.addLayer(layers[key]);
        } else {
            map.removeLayer(layers[key]);
        }
    });
});

// LOADING SIDEBAR
function showLoading() {
    sidebarContent.innerHTML = `
        <div class="p-5 animate-pulse space-y-3">
            <div class="h-5 bg-gray-200 rounded"></div>
            <div class="h-5 bg-gray-200 rounded"></div>
            <div class="h-5 bg-gray-200 rounded"></div>
        </div>
    `;
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
        ${images
            .map(
                (img) => `
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
        `,
            )
            .join("")}
    </div>`;
}

function renderLampiran(data) {
    const lampiran = data?.data?.media?.lampiran ?? [];
    const extColors = {
        PDF: { bg: "#fef2f2", text: "#dc2626" },
        DOC: { bg: "#eff6ff", text: "#2563eb" },
        DOCX: { bg: "#eff6ff", text: "#2563eb" },
        XLS: { bg: "#f0fdf4", text: "#16a34a" },
        XLSX: { bg: "#f0fdf4", text: "#16a34a" },
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
        ${lampiran
            .map((item) => {
                const ext = (item.file.split(".").pop() || "").toUpperCase();
                const c = extColors[ext] ?? { bg: "#f3f4f6", text: "#374151" };
                return `<a href="/storage/lampiran/${item.file}" target="_blank"
                class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-300 hover:bg-gray-50 transition group">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center text-[10px] font-bold"
                    style="background:${c.bg};color:${c.text};">${ext || "FILE"}</div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-gray-800 truncate">${item.nama ?? item.file}</div>
                    <div class="text-[10px] text-gray-400 truncate mt-0.5">${item.file}</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300 group-hover:text-gray-500 flex-shrink-0 transition" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </a>`;
            })
            .join("")}
    </div>`;
}

// RENDER SIDEBAR
function renderSidebar(data) {
    const status = data.data.atribut.status;
    const colors = {
        aktif: {
            dot: "var(--color-status-aktif)",
            bg: "#fef2f2",
            text: "var(--color-status-aktif)",
        },
        potensi: {
            dot: "var(--color-status-potensi)",
            bg: "#eff8fb",
            text: "#1d7a95",
        },
        draft: {
            dot: "var(--color-status-draft)",
            bg: "#f5f5f0",
            text: "var(--color-status-draft)",
        },
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
                    <p class="text-[11px] text-gray-400 truncate">${data.data.lokasi.provinsi} &mdash; ${data.data.lokasi.kabkota}</p>
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
                            ["Provinsi", data.data.lokasi.provinsi],
                            ["Kab / Kota", data.data.lokasi.kabkota],
                            ["Kecamatan", data.data.lokasi.kecamatan],
                            ["Desa", data.data.lokasi.desa],
                        ]
                            .map(
                                ([label, val]) => `
                            <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                                <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">${label}</div>
                                <div class="text-xs font-semibold text-gray-800 leading-snug">${val ?? "—"}</div>
                            </div>`,
                            )
                            .join("")}
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
                            <div class="text-xs font-semibold text-gray-800">${data.data.atribut.group ?? "—"}</div>
                        </div>
                        <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                            <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Perusahaan</div>
                            <div class="text-xs font-semibold text-gray-800 leading-snug">${data.data.atribut.perusahaan ?? "—"}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                            <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Luas Ha</div>
                            <div class="text-xs font-semibold text-gray-800">${data.data.atribut.luas != null ? Number(data.data.atribut.luas) : "—"}</div>
                        </div>
                        <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                            <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">KK</div>
                            <div class="text-xs font-semibold text-gray-800 leading-snug">${data.data.atribut.kk ?? "—"}</div>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-gray-100"></div>

                <div class="space-y-3">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Deskripsi</p>
                    <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Deskripsi Konflik</div>
                        <div class="text-xs text-gray-700 whitespace-pre-line leading-relaxed">${data.data.deskripsi.konflik ?? "—"}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Deskripsi Perjuangan</div>
                        <div class="text-xs text-gray-700 whitespace-pre-line leading-relaxed">${data.data.deskripsi.perjuangan ?? "—"}</div>
                    </div>
                </div>

                <div class="h-px bg-gray-100"></div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Instansi</p>
                    <div class="bg-gray-50 rounded-xl px-3 py-2.5">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Lembaga Pendamping</div>
                        <div class="text-xs font-semibold text-gray-800 leading-snug">${data.data.lembaga ?? "—"}</div>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'lampiran'" class="px-5 py-4 pb-6">
                ${renderLampiran(data)}
            </div>

            <div x-show="tab === 'media'" class="px-5 py-4 pb-6">
                ${renderMedia(data)}
            </div>

            <div x-show="tab === 'artikel'" class="px-5 py-4 pb-6">
                ${
                    data.data.artikel && data.data.artikel.length > 0
                        ? `<div class="space-y-4">
                ${data.data.artikel
                    .map(
                        (item) => `

                <a href="${item.sumber ?? "#"}"
                target="_blank"
                rel="noopener noreferrer"
                class="block border border-gray-100 rounded-xl overflow-hidden hover:border-gray-300 hover:bg-gray-50 transition">

                    ${
                        item.gambar
                            ? `<img src="/storage/${item.gambar}" class="w-full h-44 object-cover">`
                            : `<div class="w-full h-44 bg-gray-100 flex items-center justify-center text-gray-300 text-xs">Tidak ada gambar</div>`
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
                                : ""
                        }

                        <p class="text-[10px] text-gray-400 pt-1">
                            ${item.tanggal_publish ?? "-"}
                        </p>
                    </div>

                </a>

                `,
                    )
                    .join("")}
                </div>`
                        : `<div class="flex flex-col items-center justify-center py-10 text-center">
                    <p class="text-xs font-medium text-gray-400">Belum ada artikel</p>
                    <p class="text-[10px] text-gray-300 mt-1">
                        Klik Tambah untuk menambahkan artikel terkait
                    </p>
                </div>`
                }
                </div>

                        </div>
                    `;

    setTimeout(() => {
        if (window.lightbox) window.lightbox.destroy();
        window.lightbox = GLightbox({
            selector: ".glightbox",
            touchNavigation: true,
            loop: false,
        });
    }, 0);
}

// ── PRUNE CLUSTER: Custom cluster icon ────────────────────────────────
// pruneCluster.BuildLeafletClusterIcon = function (cluster) {
//     const count = cluster.population;
//     let size, fontSize;

//     if (count < 10) {
//         size = 34; fontSize = '12px';
//     } else if (count < 100) {
//         size = 40; fontSize = '13px';
//     } else {
//         size = 48; fontSize = '14px';
//     }

//     const html = `
//         <div style="
//             width:${size}px;
//             height:${size}px;
//             background:#890620;
//             border:3px solid #ffffff;
//             border-radius:50%;
//             display:flex;
//             align-items:center;
//             justify-content:center;
//             color:#fff;
//             font-size:${fontSize};
//             font-weight:700;
//             box-shadow:0 2px 8px rgba(137,6,32,0.4);
//         ">${count}</div>
//     `;

//     return L.divIcon({
//         html,
//         className: '',
//         iconSize: L.point(size, size),
//         iconAnchor: L.point(size / 2, size / 2)
//     });
// };

// ── Cluster icon ──────────────────────────────────────────────────────
pruneCluster.BuildLeafletClusterIcon = function (cluster) {
    var aktif = cluster.stats[0] || 0;
    var potensi = cluster.stats[1] || 0;
    // var draft = cluster.stats[2] || 0;
    var total = aktif + potensi;
    var size = 38,
        r = size / 2;
    var html;

    if (aktif > 0 && potensi > 0) {
        html = `<div style="width:${size}px;height:${size}px;border-radius:50%;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.22);display:flex;flex-direction:column;position:relative;font:bold 11px system-ui,sans-serif;">
            <div style="flex:1;background:var(--color-status-aktif);"></div>
            <div style="flex:1;background:var(--color-status-potensi);"></div>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:white;text-shadow:0 1px 4px rgba(0,0,0,0.5);">${total}</div>
        </div>`;
    } else if (aktif > 0) {
        html = `<div style="width:${size}px;height:${size}px;border-radius:50%;background:var(--color-status-aktif);color:white;display:flex;align-items:center;justify-content:center;font:bold 11px system-ui,sans-serif;box-shadow:0 2px 8px rgba(137,6,32,0.4);">${aktif}</div>`;
    } else {
        html = `<div style="width:${size}px;height:${size}px;border-radius:50%;background:var(--color-status-potensi);color:white;display:flex;align-items:center;justify-content:center;font:bold 11px system-ui,sans-serif;box-shadow:0 2px 8px rgba(52,138,167,0.4);">${potensi}</div>`;
    }
    return L.divIcon({
        className: "",
        iconSize: [size, size],
        iconAnchor: [r, r],
        html,
    });
};
pruneCluster.Cluster.Size = 50;

// ── Marker icons ──────────────────────────────────────────────────────
const iconAktif = L.divIcon({
    className: "",
    iconSize: [20, 20],
    iconAnchor: [10, 10],
    popupAnchor: [0, -13],
    html: `<div style="width:20px;height:20px;border-radius:50%;background:var(--color-status-aktif);border:2.5px solid white;box-shadow:0 1px 6px rgba(137,6,32,0.45);"></div>`,
});
const iconPotensi = L.divIcon({
    className: "",
    iconSize: [20, 20],
    iconAnchor: [10, 10],
    popupAnchor: [0, -13],
    html: `<div style="width:20px;height:20px;border-radius:50%;background:white;border:3px solid var(--color-status-potensi);box-shadow:0 1px 6px rgba(52,138,167,0.35);"></div>`,
});

// PrepareLeafletMarker dipanggil untuk setiap marker individual (bukan cluster)
// Kita override ini untuk menambahkan click handler pada marker
pruneCluster.PrepareLeafletMarker = function (leafletMarker, data) {
    const status = data.status?.toLowerCase();
    let icon;

    if (status === "aktif") {
        icon = iconAktif;
    } else if (status === "potensi") {
        icon = iconPotensi;
    } else {
        icon = iconPotensi;
    }

    leafletMarker.setIcon(icon);

    const id = data.id;

    if (leafletMarker._konflikClickBound) {
        leafletMarker.off("click", leafletMarker._konflikClickBound);
    }

    leafletMarker._konflikClickBound = function () {
        markerClicked = true; // Set flag untuk mencegah map click menangani ini
        console.log("Marker klik, id:", id);
        openSidebar();
        showLoading();
        updateSelectedKonflik(id);

        fetch(`/cms/rest-map/${id}?source=public`)
            .then((res) => {
                if (!res.ok) throw new Error("HTTP error " + res.status);
                return res.json();
            })
            .then((res) => {
                console.log("Response:", res);
                if (!res || !res.data) throw new Error("Data kosong");
                renderSidebar(res);
            })
            .catch((err) => {
                console.error("Fetch error:", err);
                sidebarContent.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12 px-8 text-center">
                        <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-600">Gagal memuat data</p>
                        <p class="text-xs text-gray-400 mt-1">ID: ${id}, ${err.message}</p>
                    </div>
                `;
            });
    };

    leafletMarker.on("click", leafletMarker._konflikClickBound);
};

// ── CLUSTER CLICK HANDLER ────────────────────────────────────────
function showClusterKonflikList(clusterMarkers, originalLat, originalLng) {
    // Urutkan berdasarkan status: aktif dulu, lalu potensi
    clusterMarkers.sort((a, b) => {
        const statusA = (a.status || "").toLowerCase();
        const statusB = (b.status || "").toLowerCase();
        if (statusA === "aktif" && statusB !== "aktif") return -1;
        if (statusA !== "aktif" && statusB === "aktif") return 1;
        return 0;
    });

    const html = `
        <div class="p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Pilih Konflik</h3>
            <p class="text-xs text-gray-400 mb-3">${clusterMarkers.length} konflik di lokasi ini</p>
            <div class="space-y-2 max-h-[400px] overflow-y-auto">
                ${clusterMarkers
                    .map((m) => {
                        const isAktif =
                            (m.status || "").toLowerCase() === "aktif";
                        return `
                    <button type="button"
                        onclick="pickKonflik(${m.id})"
                        class="w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-gray-50 focus-visible:bg-gray-50 transition group">
                        <span aria-hidden="true" class="w-2 h-2 rounded-full flex-shrink-0 ${isAktif ? "bg-status-aktif" : "bg-status-potensi"}"></span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-baseline justify-between gap-2">
                                <span class="text-[13px] font-medium text-gray-900 truncate group-hover:text-gray-950">
                                    ${m.desa || m.kecamatan || m.kabkota || "Tanpa nama"}
                                </span>
                                <span class="font-mono text-[9px] uppercase tracking-wider flex-shrink-0 ${isAktif ? "text-status-aktif" : "text-status-potensi"}">${m.status}</span>
                            </span>
                            <span class="block text-[11px] text-gray-400 truncate">${m.kabkota || ""}${m.provinsi ? ", " + m.provinsi : ""}</span>
                            <span class="block mt-1 font-mono text-[10px] text-gray-400 tabular-nums">
                                ${Number(m.luas || 0).toLocaleString("id-ID")} ha · ${Number(m.kk || 0).toLocaleString("id-ID")} KK
                            </span>
                        </span>
                    </button>
                    `;
                    })
                    .join("")}
            </div>
        </div>
    `;

    sidebarContent.innerHTML = html;
    openSidebar();
}

function pickKonflik(id) {
    // Fungsi ini HANYA dipanggil dari daftar pilihan konflik (showClusterKonflikList),
    // dan daftar itu sendiri baru muncul SETELAH peta sudah di-zoom ke titik yang
    // sama persis (dari focusKonflik atau klik cluster di peta). Jadi di sini
    // TIDAK perlu map.setView() lagi sama sekali — peta sudah pasti di lokasi itu.
    // Memanggil map.setView ulang (walau ke koordinat "yang sama") adalah
    // penyebab sensasi "kedut/kedip" sebelumnya, karena animasi zoom sebelumnya
    // belum tentu benar-benar selesai persis di saat dicek.
    openSidebar();
    showLoading();
    updateSelectedKonflik(id);
    fetch(`/cms/rest-map/${id}?source=public`)
        .then((res) => {
            if (!res.ok) throw new Error("HTTP error " + res.status);
            return res.json();
        })
        .then((res) => {
            if (!res || !res.data) throw new Error("Data kosong");
            renderSidebar(res);
        })
        .catch((err) => {
            console.error("Fetch error:", err);
            sidebarContent.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 px-8 text-center">
                    <p class="text-sm font-medium text-gray-600">Gagal memuat data</p>
                    <p class="text-xs text-gray-400 mt-1">ID: ${id}, ${err.message}</p>
                </div>`;
        });
}

// ── FETCH & TAMBAW MARKER KE PRUNE CLUSTER ────────────────────────────
const initialSelectedId = getSelectedKonflik();

fetch("/cms/rest-map/")
    .then((res) => res.json())
    .then((data) => {
        if (!data.features) return;

        // Simpan semua data untuk lookup koordinat di focusKonflik
        allKonflikData = data.features;

        let selectedMarker = null;

        // ── Deteksi koordinat yang sama persis (konflik "double") ──────
        // PruneCluster mengelompokkan marker berdasarkan jarak PIKSEL di peta.
        // Kalau dua konflik punya lat/lng yang PERSIS sama, jarak piksel di
        // antara keduanya akan selalu 0 di zoom berapa pun — jadi cluster-nya
        // TIDAK PERNAH bisa pecah walau sudah di-zoom sampai level 18.
        // Fix: sebar sedikit (jitter) posisi VISUAL marker yang koordinatnya
        // identik dalam radius kecil (~55m). Di zoom jauh, jarak ini masih
        // kecil secara piksel sehingga tetap tergabung jadi satu cluster
        // (tidak mengubah tampilan biasa). Di zoom 18 (saat cluster diklik
        // atau salah satu konflik dipilih dari daftar), jarak ini sudah
        // cukup besar secara piksel sehingga marker terpisah dan cluster
        // benar-benar pecah. Data asli (lat/lng, info lokasi, dst) tetap
        // disimpan utuh di marker.data, jadi tidak memengaruhi fetch detail
        // maupun tampilan koordinat di sidebar.
        const coordGroups = {};
        data.features.forEach((item) => {
            const lat = parseFloat(item.properties.lat);
            const lng = parseFloat(item.properties.long);
            if (isNaN(lat) || isNaN(lng)) return;
            const status = item.properties.status?.toLowerCase();
            if (status !== "aktif" && status !== "potensi") return;

            const key = `${lat.toFixed(6)},${lng.toFixed(6)}`;
            if (!coordGroups[key]) coordGroups[key] = [];
            coordGroups[key].push(item);
        });

        data.features.forEach((item) => {
            const lat = parseFloat(item.properties.lat);
            const lng = parseFloat(item.properties.long);

            if (isNaN(lat) || isNaN(lng)) return;

            const status = item.properties.status?.toLowerCase();

            // hanya tampil aktif
            if (status !== "aktif" && status !== "potensi") return;

            // Cek apakah item ini berbagi koordinat persis dengan item lain
            const key = `${lat.toFixed(6)},${lng.toFixed(6)}`;
            const group = coordGroups[key];

            let markerLat = lat;
            let markerLng = lng;

            if (group.length > 1) {
                const posInGroup = group.indexOf(item);
                const radiusDeg = 0.0005; // ~55m, cukup agar terpisah di zoom 18
                const angle = (2 * Math.PI * posInGroup) / group.length;
                markerLat = lat + radiusDeg * Math.cos(angle);
                markerLng = lng + radiusDeg * Math.sin(angle);
            }

            // Buat PruneCluster.Marker (bukan L.marker) — pakai posisi yang
            // sudah di-jitter untuk RENDER, tapi data asli tetap lat/lng asal
            const marker = new PruneCluster.Marker(markerLat, markerLng);

            // Simpan id di data marker untuk diakses saat klik
            marker.data.id = item.properties.id;
            marker.data.status = item.properties.status;
            marker.data.lat = lat;
            marker.data.lng = lng;
            marker.data.desa = item.properties.desa;
            marker.data.kecamatan = item.properties.kecamatan;
            marker.data.kabkota = item.properties.kabkota;
            marker.data.provinsi = item.properties.provinsi;
            marker.data.luas = item.properties.luas;
            marker.data.kk = item.properties.kk;

            pruneCluster.RegisterMarker(marker);

            // Simpan marker berdasarkan koordinat untuk cluster lookup
            const coordKey = `${lat},${lng}`;
            if (!markersByCoord[coordKey]) {
                markersByCoord[coordKey] = [];
            }
            markersByCoord[coordKey].push(marker.data);

            if (status === "aktif") {
                marker.category = 0;
                markersAktif.push(marker);
            }

            if (status === "potensi") {
                marker.category = 1;
                markersPotensi.push(marker);
            }

            if (String(marker.data.id) === initialSelectedId) {
                selectedMarker = marker;
            }
        });

        // Tambahkan pruneCluster ke map setelah semua marker terdaftar
        map.addLayer(pruneCluster);

        // Event click pada map untuk mendeteksi cluster click dari peta
        // Karena PruneCluster tidak memiliki event khusus untuk cluster, kita pakai map.on('click')
        // dan deteksi apakah click adalah pada cluster dengan population > 1
        map.on("click", function (e) {
            // Jika marker sudah diklik, skip (marker click sudah dihandle oleh PrepareLeafletMarker)
            if (markerClicked) {
                markerClicked = false;
                return;
            }

            // Cek apakah yang diklik adalah cluster dengan mencari marker terdekat
            const cluster = pruneCluster.Cluster.FindClosest(e.latlng, 10);
            if (cluster && cluster.population > 1) {
                // Ini cluster - ambil data konflik LANGSUNG dari marker.data
                // di dalam cluster (koordinat ASLI, bukan posisi hasil jitter),
                // supaya tidak perlu lagi mencocokkan lewat posKey yang bisa
                // meleset karena marker duplikat koordinat sudah digeser sedikit.
                const clusterConflicts = cluster.markers.map((m) => ({
                    id: m.data.id,
                    status: m.data.status,
                    lat: m.data.lat,
                    lng: m.data.lng,
                    desa: m.data.desa,
                    kecamatan: m.data.kecamatan,
                    kabkota: m.data.kabkota,
                    provinsi: m.data.provinsi,
                    luas: m.data.luas,
                    kk: m.data.kk,
                }));

                // Zoom ke titik cluster dulu baru tampilkan daftar pilihan
                map.setView(e.latlng, 18, { animate: true });
                setTimeout(() => {
                    showClusterKonflikList(
                        clusterConflicts,
                        e.latlng.lat,
                        e.latlng.lng,
                    );
                }, 300);
            }
        });

        if (selectedMarker) {
            map.setView(
                [selectedMarker.data.lat, selectedMarker.data.lng],
                12,
                { animate: true },
            );
            openSidebar();
            showLoading();
            fetch(`/cms/rest-map/${selectedMarker.data.id}?source=public`)
                .then((res) => {
                    if (!res.ok) throw new Error("HTTP error " + res.status);
                    return res.json();
                })
                .then((res) => {
                    if (!res || !res.data) throw new Error("Data kosong");
                    renderSidebar(res);
                })
                .catch((err) => {
                    console.error("Fetch error:", err);
                    sidebarContent.innerHTML = `
                        <div class="flex flex-col items-center justify-center py-12 px-8 text-center">
                            <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600">Gagal memuat data</p>
                            <p class="text-xs text-gray-400 mt-1">ID: ${selectedMarker.data.id}, ${err.message}</p>
                        </div>`;
                });
        }
    })
    .catch((err) => console.log(err));

// TOGGLE TITIK KONFLIK (opsional, jika ada checkbox untuk layer konflik)
const konflikCheckbox = document.getElementById("adminkabkota");

if (konflikCheckbox) {
    konflikCheckbox.addEventListener("change", function () {
        if (this.checked) {
            map.addLayer(pruneCluster);
        } else {
            map.removeLayer(pruneCluster);
            closeSidebar();
        }
    });
}

function applyFilterStatus() {
    markersAktif.forEach((marker) => {
        marker.filtered = !isAktifVisible;
    });

    markersPotensi.forEach((marker) => {
        marker.filtered = !isPotensiVisible;
    });

    pruneCluster.ProcessView();
}

document.getElementById("toggleAktif").addEventListener("click", function () {
    isAktifVisible = !isAktifVisible;
    document
        .getElementById("toggleAktif")
        .setAttribute("aria-pressed", String(isAktifVisible));
    this.classList.toggle("opacity-40");
    applyFilterStatus();
});

document.getElementById("togglePotensi").addEventListener("click", function () {
    isPotensiVisible = !isPotensiVisible;
    document
        .getElementById("togglePotensi")
        .setAttribute("aria-pressed", String(isPotensiVisible));
    this.classList.toggle("opacity-40");
    applyFilterStatus();
});
