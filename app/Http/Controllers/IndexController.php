<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    // Layers the map is allowed to query — keeps this proxy from becoming an open WMS relay
    private static function wmsLayers(): array
    {
        return array_values(config('geoserver.layers'));
    }

    public function wmsFeatureInfo(Request $request)
    {
        $data = $request->validate([
            'layers' => 'required|string|in:' . implode(',', self::wmsLayers()),
            'bbox'   => 'required|regex:/^-?\d+(\.\d+)?(,-?\d+(\.\d+)?){3}$/',
            'width'  => 'required|integer|min:1|max:4000',
            'height' => 'required|integer|min:1|max:4000',
            'x'      => 'required|integer|min:0',
            'y'      => 'required|integer|min:0',
        ]);

        $response = Http::get(config('geoserver.url').'/wms', [
            'SERVICE'       => 'WMS',
            'VERSION'       => '1.1.1',
            'REQUEST'       => 'GetFeatureInfo',
            'LAYERS'        => $data['layers'],
            'QUERY_LAYERS'  => $data['layers'],
            'STYLES'        => '',
            'BBOX'          => $data['bbox'],
            'WIDTH'         => $data['width'],
            'HEIGHT'        => $data['height'],
            'X'             => $data['x'],
            'Y'             => $data['y'],
            'SRS'           => 'EPSG:3857',
            'INFO_FORMAT'   => 'application/json',
            'FEATURE_COUNT' => 5,
        ]);

        // Error dari GeoServer datang sebagai XML/HTML — teruskan apa adanya
        $body = $response->successful() ? $response->json() : null;
        if (! is_array($body) || ! isset($body['features'])) {
            return response($response->body(), $response->status())
                ->header('Content-Type', 'application/json');
        }

        if ($data['layers'] === config('geoserver.layers.pbph')) {
            $body['features'] = $this->enrichPbph($body['features']);
        }

        return response()->json($body, $response->status());
    }

    /**
     * Sisipkan info kurasi PBPH (dari CMS) ke properti tiap feature, supaya popup
     * peta tidak perlu request kedua. Konsesi tanpa data info dibiarkan apa adanya.
     * Konflik tidak ikut dikirim ke popup peta — endpoint ini publik, sedangkan
     * data konflik tunduk pada aturan "draft cuma untuk pemiliknya".
     */
    private function enrichPbph(array $features): array
    {
        $codes = collect($features)->pluck('properties.kode_pbph')->filter()->unique();
        $infos = $codes->isNotEmpty()
            ? DB::table('pbph_info')->whereIn('kode_pbph', $codes)->get()->keyBy('kode_pbph')
            : collect();

        $lampirans = $infos->isNotEmpty()
            ? DB::table('pbph_lampiran')
                ->whereIn('pbph_info_id', $infos->pluck('id'))
                ->orderBy('id')
                ->get(['pbph_info_id', 'nama', 'file'])
                ->groupBy('pbph_info_id')
            : collect();

        foreach ($features as $i => $feature) {
            $info = $infos->get($feature['properties']['kode_pbph'] ?? null);
            if (! $info) {
                continue;
            }

            $features[$i]['properties']['info'] = [
                'nama_perusahaan' => $info->nama_perusahaan,
                'izin_pertama' => $info->izin_pertama,
                'izin_saat_ini' => $info->izin_saat_ini,
                'luas' => $info->luas === null ? null : (float) $info->luas,
                'komisaris' => $info->komisaris,
                'direktur_utama' => $info->direktur_utama,
                'direktur' => $info->direktur,
                'lampiran' => $lampirans->get($info->id, collect())
                    ->map(fn ($l) => [
                        'nama' => $l->nama,
                        'berkas' => $l->file,
                        'url' => Storage::url('pbph-lampiran/'.$l->file),
                    ])
                    ->values(),
            ];
        }

        return $features;
    }

    public function index(){
        $title = 'Jaga Kampung';

        // Only conflicts that actually render on the map
        $base = DB::table('konflik')->whereIn('status', ['aktif', 'potensi']);

        $stats = [
            'total'    => (clone $base)->count(),
            'aktif'    => (clone $base)->where('status', 'aktif')->count(),
            'potensi'  => (clone $base)->where('status', 'potensi')->count(),
            'luas'     => (clone $base)->get()->sum(fn ($r) => round((float) $r->luas)),
            'kk'       => (int) (clone $base)->sum('kk'),
            'jiwa'     => (int) (clone $base)->sum('jiwa'),
            'provinsi' => (clone $base)->distinct('provinsi')->count('provinsi'),
        ];

        $konfliks = (clone $base)
            ->select('id', 'desa', 'kecamatan', 'kabkota', 'provinsi', 'status', 'luas', 'kk', 'jiwa', 'lat', 'long')
            ->orderByRaw("CASE status WHEN 'aktif' THEN 0 WHEN 'potensi' THEN 1 ELSE 2 END")
            ->orderByDesc('luas')
            ->get();

        return view('frontends.index', compact('title', 'stats', 'konfliks'));
    }
}
