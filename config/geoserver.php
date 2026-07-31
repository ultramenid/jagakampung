<?php

// Nama layer sering berganti tiap rilis data (lihat riwayat git: "change pbph
// layer", "update PBPH layer to JULI2026"). Satu tempat, supaya rename
// berikutnya cukup satu baris. public/js/map.js masih punya salinannya sendiri
// karena JS statis tidak bisa membaca config PHP.
return [
    'url' => 'https://geoserver.jagakampung.id/geoserver',

    'layers' => [
        'kawasan_hutan' => 'jagamkampung:KH2025',
        'pbph' => 'jagamkampung:PBPH_JULI2026',
    ],
];
