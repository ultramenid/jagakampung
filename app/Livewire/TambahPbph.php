<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPbphLampiran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class TambahPbph extends Component
{
    use WithFileUploads, HandlesPbphLampiran;

    public $kode_pbph, $izin_pertama, $izin_saat_ini, $luas, $komisaris, $direktur_utama, $direktur;
    public $konflikIds = [];
    public $filterPerusahaan = '';
    // pencarian konsesi: $cariKonsesi = kata kunci, $namaKonsesi = label yang tampil di tombol
    public $cariKonsesi = '', $namaKonsesi = 'Pilih konsesi';

    // nama dicari sendiri dari daftar, bukan dikirim lewat atribut klik — nama
    // perusahaan mengandung karakter seperti "&" yang bikin repot di HTML/JS.
    public function selectKonsesi($kode){
        $konsesi = collect($this->getKonsesi())->firstWhere('kode_pbph', $kode);
        if (! $konsesi) {
            return;
        }

        $this->kode_pbph = $kode;
        $this->namaKonsesi = $konsesi['namobj'].' ('.$kode.')';
        $this->cariKonsesi = '';
        $this->dispatch('close-konsesi');
    }

    public function storeDatabase(){
        $this->authorizeAdmin();

        if(! $this->manualValidation()){
            return;
        }

        $konsesi = collect($this->getKonsesi())->firstWhere('kode_pbph', $this->kode_pbph);

        DB::transaction(function () use ($konsesi) {
            $id = DB::table('pbph_info')->insertGetId([
                'kode_pbph' => $this->kode_pbph,
                // disalin dari layer supaya daftar CMS bisa dicari tanpa memanggil GeoServer
                'nama_perusahaan' => $konsesi['namobj'] ?? null,
                'izin_pertama' => trim($this->izin_pertama),
                'izin_saat_ini' => trim($this->izin_saat_ini),
                'luas' => (float) $this->luas,
                'komisaris' => trim($this->komisaris),
                'direktur_utama' => trim($this->direktur_utama),
                'direktur' => trim($this->direktur),
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);

            $this->syncKonflik($id);
            $this->simpanLampiran($id);
        });

        redirect()->to('/cms/pbph');
    }

    public function manualValidation(){
        if($this->kode_pbph == ''){
            Toaster::error('Pilih salah satu konsesi PBPH!');
            return;
        }elseif(! collect($this->getKonsesi())->firstWhere('kode_pbph', $this->kode_pbph)){
            // kode datang dari klik pengguna — pastikan benar-benar ada di layer PBPH
            Toaster::error('Konsesi tidak dikenal!');
            return;
        }elseif(DB::table('pbph_info')->where('kode_pbph', $this->kode_pbph)->exists()){
            Toaster::error('Konsesi ini sudah punya data info!');
            return;
        }elseif(trim((string) $this->izin_pertama) == ''){
            Toaster::error('Izin pertama harus diisi!');
            return;
        }elseif(trim((string) $this->izin_saat_ini) == ''){
            Toaster::error('Izin saat ini harus diisi!');
            return;
        }elseif(trim((string) $this->luas) == ''){
            Toaster::error('Luas harus diisi!');
            return;
        }elseif(! is_numeric($this->luas)){
            Toaster::error('Luas harus berupa angka!');
            return;
        }elseif(trim((string) $this->komisaris) == ''){
            Toaster::error('Komisaris harus diisi!');
            return;
        }elseif(trim((string) $this->direktur_utama) == ''){
            Toaster::error('Direktur utama harus diisi!');
            return;
        }elseif(trim((string) $this->direktur) == ''){
            Toaster::error('Direktur harus diisi!');
            return;
        }
        // Konflik terkait tetap opsional — sebagian besar konsesi memang tidak
        // punya konflik tercatat, jadi mewajibkannya akan mengunci form.
        return true;
    }

    protected function syncKonflik($pbphInfoId){
        DB::table('konflik_pbph')->where('pbph_info_id', $pbphInfoId)->delete();

        $rows = DB::table('konflik')
            ->whereIn('id', array_filter((array) $this->konflikIds))
            ->pluck('id')
            ->map(fn ($konflikId) => [
                'pbph_info_id' => $pbphInfoId,
                'konflik_id' => $konflikId,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ])
            ->all();

        if ($rows) {
            DB::table('konflik_pbph')->insert($rows);
        }
    }

    /**
     * Daftar konsesi dari GeoServer (WFS) — 575 poligon, 572 kode unik.
     * Layer PBPH tidak ada di database aplikasi maupun di koneksi pgsql_gis.
     */
    public function getKonsesi(){
        $cached = Cache::get('pbph_konsesi');
        if ($cached) {
            return $cached;
        }

        $response = Http::timeout(20)->get(config('geoserver.url').'/wfs', [
            'service' => 'WFS',
            'version' => '1.1.0',
            'request' => 'GetFeature',
            'typeName' => config('geoserver.layers.pbph'),
            'outputFormat' => 'application/json',
            'propertyName' => 'kode_pbph,namobj',
        ]);

        if (! $response->successful()) {
            return [];
        }

        $list = collect($response->json('features') ?? [])
            ->pluck('properties')
            ->filter(fn ($p) => ! empty($p['kode_pbph']))
            ->unique('kode_pbph') // sebagian konsesi terpecah jadi beberapa poligon
            ->sortBy('namobj')
            ->values()
            ->all();

        // hanya di-cache kalau isinya benar — jangan kunci daftar kosong selama sehari
        if ($list) {
            Cache::put('pbph_konsesi', $list, now()->addDay());
        }

        return $list;
    }

    private function authorizeAdmin(){
        if ((int) session('role_id') !== 0) {
            abort(403, 'Akses terbatas untuk administrator.');
        }
    }

    public function render(){
        $terpakai = DB::table('pbph_info')->pluck('kode_pbph')->all();
        $cari = trim($this->cariKonsesi);

        // 572 konsesi sudah ada di memori/cache — cukup disaring di PHP, tanpa query
        $konsesis = collect($this->getKonsesi())
            ->reject(fn ($k) => in_array($k['kode_pbph'], $terpakai, true))
            ->when($cari !== '', fn ($list) => $list->filter(
                fn ($k) => stripos($k['namobj'].' '.$k['kode_pbph'], $cari) !== false
            ))
            ->take(50)
            ->values();

        return view('livewire.tambah-pbph', [
            'konsesis' => $konsesis,
            'konfliks' => $this->getKonflik(),
            'perusahaans' => $this->getPerusahaanKonflik(),
        ]);
    }

    /**
     * Kandidat konflik: disaring per perusahaan, tapi yang sudah dicentang selalu
     * ikut tampil — kalau tidak, ganti filter akan menyembunyikan pilihan sendiri.
     */
    protected function getKonflik(){
        $terpilih = array_filter((array) $this->konflikIds);

        return DB::table('konflik')
            ->select('id', 'desa', 'kecamatan', 'kabkota', 'provinsi', 'status', 'perusahaan')
            ->when($this->filterPerusahaan !== '', fn ($q) => $q->where(function ($q) use ($terpilih) {
                $q->where('perusahaan', $this->filterPerusahaan);
                if ($terpilih) {
                    $q->orWhereIn('id', $terpilih);
                }
            }))
            ->orderBy('perusahaan')
            ->orderBy('provinsi')
            ->get();
    }

    protected function getPerusahaanKonflik(){
        return DB::table('konflik')
            ->whereNotNull('perusahaan')
            ->where('perusahaan', '!=', '')
            ->distinct()
            ->orderBy('perusahaan')
            ->pluck('perusahaan');
    }
}
