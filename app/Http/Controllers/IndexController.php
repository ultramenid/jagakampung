<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class IndexController extends Controller
{
    // Layers the map is allowed to query — keeps this proxy from becoming an open WMS relay
    private const WMS_LAYERS = [
        'jagamkampung:KH2025',
        'jagamkampung:PBPH_JULI2026',
    ];

    public function wmsFeatureInfo(Request $request)
    {
        $data = $request->validate([
            'layers' => 'required|string|in:' . implode(',', self::WMS_LAYERS),
            'bbox'   => 'required|regex:/^-?\d+(\.\d+)?(,-?\d+(\.\d+)?){3}$/',
            'width'  => 'required|integer|min:1|max:4000',
            'height' => 'required|integer|min:1|max:4000',
            'x'      => 'required|integer|min:0',
            'y'      => 'required|integer|min:0',
        ]);

        $response = Http::get('https://geoserver.jagakampung.id/geoserver/wms', [
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

        return response($response->body(), $response->status())
            ->header('Content-Type', 'application/json');
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
