<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class TambahKonflik extends Component
{
    use WithFileUploads;

    public $deleter;

    public $isAdd;

    public $isEdit;

    public $isPelaku;

    public $chooseRegion = '';

    public $region = 'Pilih desa';

    public $administrasi = [];

    public $lampirans = [];

    public $images = [];

    public $selectedGroup;

    public $selectedPerusahaan;

    public $selectedStatus;

    public $lembaga = 'Pilih lembaga';

    public $lembagas = [];

    public $chooselembaga = '';

    public $namalampiran;

    public $filelampiran;

    public $editIndex = null;

    public $luas;

    public $kk;

    public $deskripsikonflik;

    public $deskripsiperjuangan;

    public $provinsi;

    public $kabkota;

    public $kecamatan;

    public $desa;

    public $latitude;

    public $longtitude;

    public $geom;

    public $searchPage = 1;

    public $searchHasMore = false;

    public function manualValidation()
    {
        // dd('masuk');
        if ($this->desa == '') {
            // dd('masuk');
            Toaster::error('Silahkan pilih wilayah konflik!');

            return false;
        } elseif ($this->latitude == '' || $this->longtitude == '') {
            Toaster::error('Silahkan pilih titik koordinat konflik pada peta!');

            return false;
        } elseif ($this->luas == '') {
            Toaster::error('Silahkan isi luas konflik!');

            return false;
        } elseif ($this->kk == '') {
            Toaster::error('Silahkan isi jumlah KK konflik!');

            return false;
        } elseif ($this->selectedStatus == '') {
            Toaster::error('Silahkan pilih status konflik!');

            return false;
        } elseif ($this->deskripsikonflik == '') {
            Toaster::error('Silahkan isi deskripsi konflik!');

            return false;
        } elseif ($this->deskripsiperjuangan == '') {
            Toaster::error('Silahkan isi deskripsi perjuangan!');

            return false;
        } elseif ($this->selectedGroup == '') {
            Toaster::error('Silahkan pilih group perusahaan!');

            return false;
        } elseif ($this->selectedPerusahaan == '') {
            Toaster::error('Silahkan pilih perusahaan!');

            return false;
        }

        return true;
    }

    public function storeDatabase()
    {
        // dd(session()->all());

        // kalo role nya user default ke draft
        if (session('role_id') === 1) {
            $this->selectedStatus = 'draft';
        }

        // ponytail: validate image uploads (no mimes rule existed before)
        $this->validate([
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);

        if ($this->manualValidation()) {
            // Simpan data konflik
            // dd(Auth::id(), Auth::user());
            // $status = $this->selectedStatus;
            // dd($status);

            $konflikId = DB::table('konflik')->insertGetId([
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
                'status' => $this->selectedStatus,
                'deskripsikonflik' => $this->deskripsikonflik,
                'deskripsiperjuangan' => $this->deskripsiperjuangan,
                'user_id' => session('id'),
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);

            // Simpan data lembaga terkait konflik
            foreach ($this->lembagas as $lembaga) {
                DB::table('konflik_lembaga')->insert([
                    'konflik_id' => $konflikId,
                    'nama' => $lembaga,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }

            // Simpan data lampiran terkait konflik
            foreach ($this->lampirans as $lampiran) {

                $filenamelampiran = uniqid().'_'.$lampiran['filename'];
                $pathLampiran = $lampiran['file']->storeAs('lampiran', $filenamelampiran, 'public');

                DB::table('konflik_lampiran')->insert([
                    'konflik_id' => $konflikId,
                    'nama' => $lampiran['nama'],
                    'file' => $filenamelampiran,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }

            // simpan data image terkait konflik
            foreach ($this->images as $image) {
                $filenamegambar = uniqid().'_'.$image->getClientOriginalName();
                $pathGambar = $image->storeAs('gambar', $filenamegambar, 'public');

                DB::table('konflik_gambar')->insert([
                    'konflik_id' => $konflikId,
                    'nama' => $filenamegambar,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                ]);

            }
            redirect()->to('/cms/konflik');
        }

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

    public function resetForm()
    {
        $this->namalampiran = null;
        $this->filelampiran = null;
        $this->isAdd = false;
        $this->isEdit = false;
        $this->editIndex = null;

        $this->resetValidation();
    }

    public function removeImage($index)
    {
        unset($this->images[$index]);

        $this->images = array_values($this->images);
    }

    public function simpanLampiran()
    {
        $this->validate([
            'namalampiran' => 'required|string',
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

    public function editPerkembangan($index)
    {
        if (! isset($this->lampirans[$index])) {
            return;
        }

        $this->editIndex = $index;
        $this->isEdit = true;
        $this->isAdd = false;

        $this->namalampiran = $this->lampirans[$index]['nama'];

        // file tidak bisa diprefill, biarkan kosong
        $this->filelampiran = null;
    }

    public function updateLampiranTemp()
    {
        if ($this->editIndex === null) {
            return;
        }

        $this->validate([
            'namalampiran' => 'required|string',
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

    public function delete($index)
    {
        unset($this->lampirans[$index]);

        $this->lampirans = array_values($this->lampirans);

        // kalau sedang edit item yang dihapus
        if ($this->editIndex === $index) {
            $this->resetForm();
        }
    }

    public function setLembaga($lembaga)
    {
        if (! in_array($lembaga, $this->lembagas)) {
            array_push($this->lembagas, $lembaga);
        }
        $this->chooselembaga = $lembaga;
        $this->lembaga = '';
    }

    public function deleteTags($id)
    {
        unset($this->lembagas[$id]);
        $this->lembaga = '';
        empty($this->lembagas) ? $this->lembaga = 'Pilih lembaga' : null;
        $this->chooselembaga = '';
    }

    public function updatedChooseRegion()
    {
        if (strlen($this->chooseRegion) < 3) {
            $this->administrasi = [];
            $this->searchPage = 1;
            $this->searchHasMore = false;

            return;
        }

        $this->searchPage = 1;
        $results = DB::connection('pgsql_gis')
            ->table('proteus.mv_level_6_id')
            ->select('geom', 'name', 'latitude', 'longtitude')
            ->where('name', 'ILIKE', '%'.$this->chooseRegion.'%')
            ->skip(0)
            ->take(5)
            ->get();

        $this->administrasi = $results;
        $this->searchHasMore = $results->count() === 5;
    }

    public function loadMoreResults()
    {
        if (! $this->searchHasMore || strlen($this->chooseRegion) < 3) {
            return;
        }

        $offset = $this->searchPage * 5;
        $moreResults = DB::connection('pgsql_gis')
            ->table('proteus.mv_level_6_id')
            ->select('geom', 'name', 'latitude', 'longtitude')
            ->where('name', 'ILIKE', '%'.$this->chooseRegion.'%')
            ->skip($offset)
            ->take(5)
            ->get();

        $this->administrasi = collect($this->administrasi)->concat($moreResults);
        $this->searchPage++;
        $this->searchHasMore = $moreResults->count() === 5;
    }

    public function getLembaga()
    {
        return DB::table('instansi')->get();
    }

    public function getGroup()
    {
        return DB::table('groups')->get();
    }

    public function getPerusahaan()
    {
        return DB::table('perusahaans')->where('group', $this->selectedGroup)->get();
    }

    public function addSelected($value)
    {
        if (! in_array($value, $this->selected, true)) {
            $this->selected[] = $value;
        }
    }

    public function removeSelected($index)
    {
        unset($this->selected[$index]);
        $this->selected[] = array_values($this->selected);
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

    public function render()
    {
        $groups = $this->getGroup();
        $perusahaans = $this->getPerusahaan();
        $listlembaga = $this->getLembaga();

        return view('livewire.tambah-konflik', compact('groups', 'perusahaans', 'listlembaga'));
    }
}
