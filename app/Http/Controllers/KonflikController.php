<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KonflikController extends Controller
{
    public function add(){
        $title = 'Tambah Konflik';
        $nav = 'konflik';
        return view('backends.tambahkonflik', compact('title', 'nav'));
    }

    public function index(){
        $title = "Konflik - Jagakampung";
        $nav = 'konflik';
        return view('backends.konflik', compact('nav', 'title'));
    }

    public function edit($id){
        $title = 'Edit Konflik';
        $nav = 'konflik';
        $id = $id;
        return view('backends.editkonflik', compact('title', 'nav', 'id'));
    }

    public function destroy($id){
        $konflik = DB::table('konflik')->where('id', $id)->first();
        if (! $konflik) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        // hanya admin atau pemilik data
        if (session('role_id') != 0 && $konflik->user_id != session('id')) {
            return response()->json(['status' => 'error', 'message' => 'Tidak diizinkan menghapus data ini'], 403);
        }

        // hapus file fisik (lampiran, gambar konflik, gambar artikel)
        foreach (DB::table('konflik_lampiran')->where('konflik_id', $id)->pluck('file') as $file) {
            Storage::disk('public')->delete('lampiran/' . $file);
        }
        foreach (DB::table('konflik_gambar')->where('konflik_id', $id)->pluck('nama') as $nama) {
            Storage::disk('public')->delete('gambar/' . $nama);
        }
        foreach (DB::table('artikel')->where('konflik_id', $id)->get() as $artikel) {
            if (! empty($artikel->gambar)) {
                Storage::disk('public')->delete($artikel->gambar);
            }
        }

        // hapus baris terkait lalu konflik
        DB::table('konflik_lembaga')->where('konflik_id', $id)->delete();
        DB::table('konflik_lampiran')->where('konflik_id', $id)->delete();
        DB::table('konflik_gambar')->where('konflik_id', $id)->delete();
        DB::table('artikel')->where('konflik_id', $id)->delete();
        DB::table('konflik')->where('id', $id)->delete();

        return response()->json(['status' => 'success']);
    }
}
