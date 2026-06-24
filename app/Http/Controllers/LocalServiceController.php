<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocalServiceController extends Controller
{
    public function index(){
         $data =  DB::table('konflik')
        ->select('id', 'lat', 'long', 'status', 'user_id')
        ->get();

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
                        ),
                    );
            };

        $allfeatures = array('type' => 'FeatureCollection', 'features' => $features);
        return json_encode($allfeatures, JSON_PRETTY_PRINT);
    }

    public function kasusDetail($id){
        $data = DB::table('konflik')->select('konflik.*',
            DB::raw('(SELECT JSON_ARRAYAGG(nama) FROM konflik_gambar WHERE konflik_gambar.konflik_id = konflik.id) as gambar'),
            DB::raw('(SELECT JSON_ARRAYAGG(JSON_OBJECT("nama", nama, "file", file)) FROM konflik_lampiran WHERE konflik_lampiran.konflik_id = konflik.id) as lampiran'),
            DB::raw('(SELECT JSON_ARRAYAGG(nama) FROM konflik_lembaga WHERE konflik_lembaga.konflik_id = konflik.id) as lembaga'),
            DB::raw('(SELECT JSON_ARRAYAGG(
                        JSON_OBJECT(
                            "id", id,
                            "judul_id", judul_id,
                            "judul_en", judul_en,
                            "slug", slug,
                            "gambar", gambar,
                            "deskripsi_id", deskripsi_id,
                            "deskripsi_en", deskripsi_en,
                            "tanggal_publish", tanggal_publish,
                            "sumber", sumber
                        )
                    )
                    FROM artikel
                    WHERE artikel.konflik_id = konflik.id
                ) as artikel')
        )
        ->where('konflik.id', $id)
        ->first();

        if (! $data) {
            return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

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
                    'status' => $data->status,
                    'group' => $data->group,
                    'perusahaan' => $data->perusahaan,
                ],
                'deskripsi' => [
                    'konflik' => $data->deskripsikonflik,
                    'perjuangan' => $data->deskripsiperjuangan
                ],
                'lembaga' => json_decode($data->lembaga),
                'media' => [
                    'gambar' => json_decode($data->gambar),
                    'lampiran' => json_decode($data->lampiran),
                ],
                'artikel' => json_decode($data->artikel),
                'meta' => [
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at
                ]
            ]
        ]);
    }
}
