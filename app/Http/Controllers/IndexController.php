<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
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
            'provinsi' => (clone $base)->distinct('provinsi')->count('provinsi'),
        ];

        $konfliks = (clone $base)
            ->select('id', 'desa', 'kecamatan', 'kabkota', 'provinsi', 'status', 'luas', 'kk', 'lat', 'long')
            ->orderByRaw("CASE status WHEN 'aktif' THEN 0 WHEN 'potensi' THEN 1 ELSE 2 END")
            ->orderByDesc('luas')
            ->get();

        return view('frontends.index', compact('title', 'stats', 'konfliks'));
    }
}
