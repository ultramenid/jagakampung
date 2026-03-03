<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;


class EditKonflik extends Component
{
    use WithFileUploads;
    public $deleter, $isAdd, $isEdit, $isPelaku;
    public $chooseRegion = '';
    public $region = 'Pilih desa';
    public $administrasi = [], $lampirans = [], $images = [];
    public $selectedGroup, $selectedPerusahaan, $selectedStatus;
    public $lembaga = '', $lembagas = [], $chooselembaga = '';
    public $namalampiran;
    public $filelampiran;
    public $editIndex = null;
    public $luas, $kk, $deskripsikonflik, $deskripsiperjuangan;
    public $provinsi, $kabkota, $kecamatan, $desa, $latitude, $longtitude, $geom;
    public $idDB;

    public function mount($idDB){
        // dd($idDB);
        $this->idDB = $idDB;

        $data = DB::table('konflik')->select('konflik.*',
            DB::raw('(SELECT JSON_ARRAYAGG(nama) FROM konflik_gambar WHERE konflik_gambar.konflik_id = konflik.id) as gambar'),
            DB::raw('(SELECT JSON_ARRAYAGG(JSON_OBJECT("nama", nama, "file", file)) FROM konflik_lampiran WHERE konflik_lampiran.konflik_id = konflik.id) as lampiran'),
            DB::raw('(SELECT JSON_ARRAYAGG(nama) FROM konflik_lembaga WHERE konflik_lembaga.konflik_id = konflik.id) as lembaga')
        )
        ->where('konflik.id', $idDB)
        ->first();


        // dd($data);
        $this->provinsi = $data->provinsi;
        $this->kabkota = $data->kabkota;
        $this->kecamatan = $data->kecamatan;
        $this->desa = $data->desa;
        $this->latitude = $data->lat;
        $this->longtitude = $data->long;
        $this->luas = $data->luas;
        $this->kk = $data->kk;
        $this->deskripsikonflik = $data->deskripsikonflik;
        $this->deskripsiperjuangan = $data->deskripsiperjuangan;
        $this->selectedStatus = $data->status;
        $this->selectedGroup = $data->group;
        $this->selectedPerusahaan = $data->perusahaan;
        $this->lembagas = json_decode($data->lembaga, true) ?? [];
        $this->lampirans = json_decode($data->lampiran, true) ?? [];
        $this->images = json_decode($data->gambar, true) ?? [];
        $this->chooseRegion = $data->desa;

        $this->region = "{$this->provinsi} | {$this->kabkota} | {$this->kecamatan} | {$this->desa} ";


        $geom = DB::connection('pgsql_gis')
        ->table('proteus.POLITICAL_LEVEL_6_dissolved')
        ->selectRaw('ST_AsGeoJSON(geom) as geom')
        ->where('NAME', 'ILIKE', '%' . $this->chooseRegion . '%')
        ->first();

        $this->geom = $geom->geom;
        $this->dispatch('connected', [
            'latitude' => $this->latitude,
            'longitude' => $this->longtitude,
            'geom' => $this->geom,
        ]);

    }

    public function manualValidation(){
        // dd('masuk');
        if($this->desa == ''){
            // dd('masuk');
            Toaster::error('Silahkan pilih wilayah konflik!');
            return false;
        }elseif($this->latitude == '' || $this->longtitude == ''){
            Toaster::error('Silahkan pilih titik koordinat konflik pada peta!');
            return false;
        }elseif($this->luas == ''){
            Toaster::error('Silahkan isi luas konflik!');
            return false;
        }elseif($this->kk == ''){
            Toaster::error('Silahkan isi jumlah KK konflik!');
            return false;
        }elseif($this->selectedStatus == ''){
            Toaster::error('Silahkan pilih status konflik!');
            return false;
        }elseif($this->deskripsikonflik == ''){
            Toaster::error('Silahkan isi deskripsi konflik!');
            return false;
        }elseif($this->deskripsiperjuangan == ''){
            Toaster::error('Silahkan isi deskripsi perjuangan!');
            return false;
        }elseif($this->selectedGroup == ''){
            Toaster::error('Silahkan pilih group perusahaan!');
            return false;
        }elseif($this->selectedPerusahaan == ''){
            Toaster::error('Silahkan pilih perusahaan!');
            return false;
        }

        return true;
    }

    public function storeDatabase(){
        if($this->manualValidation()){
            // Simpan data konflik
            $konflikId = DB::table('konflik')
            ->where('id', $this->idDB)
            ->update([
                'provinsi' => $this->provinsi,
                'kabkota' => $this->kabkota,
                'kecamatan' => $this->kecamatan,
                'desa' => $this->desa,
                'lat' => $this->latitude,
                'long' => $this->longtitude,
                'luas' => $this->luas,
                'group' => $this->selectedGroup,
                'perusahaan' => $this->selectedPerusahaan,
                'kk' => $this->kk,
                'deskripsikonflik' => $this->deskripsikonflik,
                'deskripsiperjuangan' => $this->deskripsiperjuangan,
                'status' => $this->selectedStatus,
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);

            // update atau insert lembaga
            foreach ($this->lembagas as $lembaga) {
                DB::table('konflik_lembaga')->updateOrInsert(
                    ['konflik_id' => $this->idDB, 'nama' => $lembaga],
                    ['updated_at' => Carbon::now('Asia/Jakarta')]
                );
            }

            // Save lampiran
            $newFilenames = [];

            foreach ($this->lampirans as $lampiran) {
                $isTemp = $lampiran['file'] instanceof TemporaryUploadedFile;

                if ($isTemp) {
                    $filenamelampiran = uniqid() . '_' .$lampiran['filename'];
                    $lampiran['file']->storeAs('lampiran', $filenamelampiran, 'public');
                } else {
                    $filenamelampiran = $lampiran['file'];
                }

                $newFilenames[] = $filenamelampiran;

                DB::table('konflik_lampiran')->updateOrInsert(
                    ['konflik_id' => $this->idDB, 'file' => $filenamelampiran],
                    [
                        'nama' => $lampiran['nama'],
                        'file' => $filenamelampiran,
                        'updated_at' => Carbon::now('Asia/Jakarta'),
                    ]
                );
            }

            // hapus file lama yang tidak ada di $newFilenames
            $oldLampirans = DB::table('konflik_lampiran')->where('konflik_id', $this->idDB)->pluck('file')->toArray();
            $deletedLampirans = array_diff($oldLampirans, $newFilenames);

            foreach ($deletedLampirans as $deleted) {
                Storage::disk('public')->delete('lampiran/' . $deleted);
                DB::table('konflik_lampiran')->where('konflik_id', $this->idDB)->where('file', $deleted)->delete();
            }





        //    save gambar
           $allImages = array_merge($this->images, $this->newImages);

            // kumpulkan semua filename yang akan disimpan
            $newFilenames = [];

            foreach ($allImages as $image) {
                $isTemp = $image instanceof TemporaryUploadedFile;

                if ($isTemp) {
                    $filenamegambar = uniqid() . '_' . $image->getClientOriginalName();
                    $image->storeAs('gambar', $filenamegambar, 'public');
                } else {
                    $filenamegambar = $image;
                }

                $newFilenames[] = $filenamegambar;
            }

            // hapus file lama yang tidak ada di $newFilenames
            $oldGambars = DB::table('konflik_gambar')->where('konflik_id', $this->idDB)->pluck('nama')->toArray();
            $deletedGambars = array_diff($oldGambars, $newFilenames);

            foreach ($deletedGambars as $deleted) {
                Storage::disk('public')->delete('gambar/' . $deleted);
                DB::table('konflik_gambar')->where('konflik_id', $this->idDB)->where('nama', $deleted)->delete();

            }

            // update DB dengan semua filename (encode jadi JSON atau sesuai struktur tabel)

            // dd($newFilenames);
           foreach ($newFilenames as $filename) {
                DB::table('konflik_gambar')
                    ->where('konflik_id', $this->idDB)
                    ->updateOrInsert(
                        ['konflik_id' => $this->idDB, 'nama' => $filename],
                        [
                            'nama' => $filename,
                            'updated_at' => Carbon::now('Asia/Jakarta'),
                        ]
                    );
            }
            redirect()->to('/cms/konflik');
        }
    }

    public function simpanLampiran()
    {
        $this->validate([
            'namalampiran'  => 'required|string',
            'filelampiran' => 'required|mimes:pdf,jpg,jpeg,png,webp',
        ]);

        $this->lampirans[] = [
            'nama' => $this->namalampiran,
            'file' => $this->filelampiran,
            'filename' => $this->filelampiran->getClientOriginalName(),

        ];

        $this->resetForm();
        // dd($this->lampirans);
        $this->dispatch('close-form');


    }

    public $newImages = [];

    public function removeNewImage($index)
    {
        unset($this->newImages[$index]);
        $this->newImages = array_values($this->newImages);
    }

    // toggle form
    public function addLampiran()
    {
        if ($this->isAdd || $this->isEdit) {
            $this->resetForm();
            return;
        }

        $this->isAdd = true;
    }


    public function updateLampiranTemp()
    {
        if ($this->editIndex === null) {
            return;
        }

        $this->validate([
            'namalampiran'  => 'required|string',
            'filelampiran' => 'nullable|mimes:pdf,jpg,jpeg,png,webp',
        ]);

        $this->lampirans[$this->editIndex]['nama']
            = $this->namalampiran;

        // kalau upload file baru → ganti
        if ($this->filelampiran) {
            $this->lampirans[$this->editIndex]['file']
                = $this->filelampiran;

            $this->lampirans[$this->editIndex]['filename']
                = $this->filelampiran->getClientOriginalName();
        }

        // penting: reindex
        $this->lampirans = array_values($this->lampirans);

        $this->resetForm();
        $this->dispatch('close-form');

    }

    public function deleteTags($id)
    {
        unset($this->lembagas[$id]);
        $this->lembaga = '';
        empty($this->lembagas) ? $this->lembaga = 'Pilih lembaga' : null;
        $this->chooselembaga = '';
    }


     public function resetForm()
    {
        $this->namalampiran   = null;
        $this->filelampiran  = null;
        $this->isAdd         = false;
        $this->isEdit        = false;
        $this->editIndex     = null;

        $this->resetValidation();
    }

    public function editPerkembangan($index)
    {
        // dd($this->lampirans[$index]);
        if (!isset($this->lampirans[$index])) {
            return;
        }

        $this->editIndex = $index;
        $this->isEdit    = true;
        $this->isAdd     = false;

        // dd($this->lampirans[$index]->nama);
        $this->namalampiran = $this->lampirans[$index]['nama'];

        // file tidak bisa diprefill, biarkan kosong
        $this->filelampiran = null;
    }

    public function selectRegion($value, $latitude, $longtitude, $geom)
    {
        // dd($latitude, $longtitude);
        $this->latitude = $latitude;
        $this->longtitude = $longtitude;
        $this->geom = $geom;

        $this->dispatch('connected', [
            'latitude' => (float) $latitude,
            'longtitude' => (float) $longtitude,
            'geom' => $geom,
        ]);
        $parts = preg_split('/[\[\]\|]+/', $value, -1, PREG_SPLIT_NO_EMPTY);

        [
            $this->desa,
            $this->kecamatan,
            $this->kabkota,
            $this->provinsi
        ] = array_slice($parts, 0, 4) + [null, null, null, null];

        $this->region = "{$this->provinsi} | {$this->kabkota} | {$this->kecamatan} | {$this->desa} ";
        $this->chooseRegion = '';
        $this->administrasi = [];

        $this->dispatch('close-region');
    }

    public function delete($index)
    {
        unset($this->lampirans[$index]);

        $this->lampirans = array_values($this->lampirans);

        // kalau sedang edit item yang dihapus
        if ($this->editIndex === $index) {
            $this->resetForm();
        }
    }
    public function updatedChooseRegion()
    {
        if (strlen($this->chooseRegion) < 3) {
            $this->administrasi = [];
            return;
        }

        $this->administrasi = DB::connection('pgsql_gis')
        ->table('proteus.POLITICAL_LEVEL_6_dissolved')
        ->selectRaw('ST_AsGeoJSON(geom) as geom, "NAME" as name, latitude, longtitude')
        ->where('NAME', 'ILIKE', '%' . $this->chooseRegion . '%')
        ->get();
    }

    public function removeImage($index)
    {
        unset($this->images[$index]);

        $this->images = array_values($this->images);
    }

    public function getPerusahaan(){
        return DB::table('perusahaans')->where('group', $this->selectedGroup)->get();
    }
    public function getLembaga(){
        return DB::table('instansi')->get();
    }
    public function getGroup(){
        return DB::table('groups')->get();
    }
    public function render()
    {
        $groups = $this->getGroup();
        $perusahaans = $this->getPerusahaan();
        $listlembaga = $this->getLembaga();
        return view('livewire.edit-konflik', compact('groups', 'perusahaans', 'listlembaga'));
    }
}
