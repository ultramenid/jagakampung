<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocalServiceController extends Controller
{
    public function index(){
        $query = DB::table('konflik')
            ->select('id', 'lat', 'long', 'status', 'user_id', 'desa', 'kecamatan', 'kabkota', 'provinsi', 'luas', 'kk', 'jiwa', 'group', 'perusahaan');

        // pengguna non-admin tidak boleh melihat konflik berstatus draft milik orang lain
        if (session('role_id') === null || (int) session('role_id') !== 0) {
            $query->where(function ($q) {
                $q->where('status', '!=', 'draft')
                  ->orWhere('user_id', session('id'));
            });
        }

        $data = $query->get();

        $original_data = json_decode($data, true);
        // dd($original_data);
        $features = array();

        foreach($original_data as $key => $value) {
            $features[] = array(
                    'type' => 'Feature',
                        'geometry' => array('type' => 'Point',
                            'coordinates' => array((float)$value['lat'],
                            (float)$value['long'])
                        ),
                        'properties' => array(
                            'id' => $value['id'],
                            'status' => $value['status'],
                            'lat' => (float)$value['lat'],
                            'long' => (float)$value['long'],
                            'user_id' => $value['user_id'],
                            'desa' => $value['desa'],
                            'kecamatan' => $value['kecamatan'],
                            'kabkota' => $value['kabkota'],
                            'provinsi' => $value['provinsi'],
                            'luas' => $value['luas'],
                            'kk' => $value['kk'],
                            'jiwa' => $value['jiwa'],
                            'group' => $value['group'],
                            'perusahaan' => $value['perusahaan'],
                        ),
                    );
            };

        $allfeatures = array('type' => 'FeatureCollection', 'features' => $features);
        return json_encode($allfeatures, JSON_PRETTY_PRINT);
    }

    public function kasusDetail(Request $request, $id){
        $isAdmin = (int) session('role_id') === 0;
        $isPublic = ! $isAdmin;

        $data = DB::table('konflik')->where('konflik.id', $id)->first();

        if (! $data) {
            return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        if ($data->status === 'draft' && ! $isAdmin && (int) ($data->user_id ?? 0) !== (int) session('id')) {
            return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        // ponytail: portable Query Builder calls instead of MySQL-only JSON_ARRAYAGG/JSON_OBJECT
        // raw SQL — production runs Postgres, which doesn't support that syntax
        $gambar = DB::table('konflik_gambar')->where('konflik_id', $id)->pluck('nama');
        $lampiran = DB::table('konflik_lampiran')->where('konflik_id', $id)->get(['nama', 'file']);
        $lembaga = DB::table('konflik_lembaga')->where('konflik_id', $id)->pluck('nama');

        $artikelQuery = DB::table('artikel')->where('konflik_id', $id);
        if ($isPublic) {
            $artikelQuery->where('status', 'publish');
        }
        $artikel = $artikelQuery->get([
            'id', 'judul_id', 'judul_en', 'slug', 'gambar',
            'deskripsi_id', 'deskripsi_en', 'tanggal_publish', 'sumber', 'status',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $data->id,
                'lokasi' => [
                    'provinsi' => $data->provinsi,
                    'kabkota' => $data->kabkota,
                    'kecamatan' => $data->kecamatan,
                    'desa' => $data->desa,
                    'koordinat' => [
                        'lat' => (float) $data->lat,
                        'lng' => (float) $data->long,
                    ]
                ],
                'atribut' => [
                    'luas' => $data->luas,
                    'kk' => $data->kk,
                    'jiwa' => $data->jiwa,
                    'status' => $data->status,
                    'group' => $data->group,
                    'perusahaan' => $data->perusahaan,
                ],
                'deskripsi' => [
                    'konflik' => $data->deskripsikonflik,
                    'perjuangan' => $data->deskripsiperjuangan
                ],
                'lembaga' => $lembaga,
                'media' => [
                    'gambar' => $gambar,
                    'lampiran' => $lampiran,
                ],
                'artikel' => $artikel,
                'meta' => [
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at
                ]
            ]
        ]);
    }
}
