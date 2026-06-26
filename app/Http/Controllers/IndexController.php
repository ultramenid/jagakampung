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
            ->orderByRaw("FIELD(status, 'aktif', 'potensi')")
            ->orderByDesc('luas')
            ->get();

        return view('frontends.index', compact('title', 'stats', 'konfliks'));
    }
}
