<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function login(){
        $title = 'Login - Jagakampung';
        return view('backends.login', compact('title'));
    }

    public function index(){
        $title = 'Dashboard - Jagakampung';
        $nav = 'dashboard';

        $stats = [
            'konflik'    => DB::table('konflik')->count(),
            'luas'       => DB::table('konflik')->get()->sum(fn ($r) => round((float) $r->luas)), // hektar terdampak
            'kk'         => (int) DB::table('konflik')->sum('kk'),     // kepala keluarga
            'jiwa'       => (int) DB::table('konflik')->sum('jiwa'),   // jiwa
            'perusahaan' => DB::table('perusahaans')->count(),
            'instansi'   => DB::table('instansi')->count(),
            'grup'       => DB::table('groups')->count(),
            'users'      => DB::table('users')->count(),
        ];

        $byStatus = DB::table('konflik')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $recent = DB::table('konflik')->latest('id')->limit(6)
            ->get(['id', 'desa', 'kabkota', 'provinsi', 'status', 'created_at']);

        $draftArtikel = DB::table('artikel')
            ->join('konflik', 'artikel.konflik_id', '=', 'konflik.id')
            ->where('artikel.status', 'draft')
            ->when(session('role_id') !== 0, fn ($q) => $q->where('konflik.user_id', session('id')))
            ->select('artikel.id', 'artikel.judul_id', 'artikel.created_at', 'konflik.desa', 'konflik.kabkota')
            ->latest('artikel.created_at')
            ->limit(10)
            ->get();

        return view('backends.dashboard', compact('title', 'nav', 'stats', 'byStatus', 'recent', 'draftArtikel'));
    }
}
