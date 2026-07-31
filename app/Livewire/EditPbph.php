<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPbphLampiran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class EditPbph extends Component
{
    use WithFileUploads, HandlesPbphLampiran;

    public $idPbph, $kode_pbph, $nama_perusahaan;
    public $izin_pertama, $izin_saat_ini, $luas, $komisaris, $direktur_utama, $direktur;
    public $konflikIds = [];
    public $filterPerusahaan = '';

    public function mount($idDB){
        $this->idPbph = $idDB;
        $data = DB::table('pbph_info')->where('id', $this->idPbph)->first();
        abort_if(! $data, 404);

        // kode_pbph tidak bisa diubah — itu kuncinya ke layer PBPH. Salah konsesi?
        // hapus lalu tambah lagi. Karena itu form edit tidak perlu memanggil GeoServer.
        $this->kode_pbph = $data->kode_pbph;
        $this->nama_perusahaan = $data->nama_perusahaan;
        $this->izin_pertama = $data->izin_pertama;
        $this->izin_saat_ini = $data->izin_saat_ini;
        $this->luas = $data->luas;
        $this->komisaris = $data->komisaris;
        $this->direktur_utama = $data->direktur_utama;
        $this->direktur = $data->direktur;

        $this->konflikIds = DB::table('konflik_pbph')
            ->where('pbph_info_id', $this->idPbph)
            ->pluck('konflik_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->muatLampiran($this->idPbph);
    }

    public function storeDatabase(){
        $this->authorizeAdmin();

        if(! $this->manualValidation()){
            return;
        }

        DB::transaction(function () {
            DB::table('pbph_info')->where('id', $this->idPbph)->update([
                'izin_pertama' => trim($this->izin_pertama),
                'izin_saat_ini' => trim($this->izin_saat_ini),
                'luas' => (float) $this->luas,
                'komisaris' => trim($this->komisaris),
                'direktur_utama' => trim($this->direktur_utama),
                'direktur' => trim($this->direktur),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);

            $this->syncKonflik();
            $this->simpanLampiran($this->idPbph);
        });

        redirect()->to('/cms/pbph');
    }

    public function manualValidation(){
        if(trim((string) $this->izin_pertama) == ''){
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
        // Konflik terkait tetap opsional — lihat catatan di TambahPbph.
        return true;
    }

    protected function syncKonflik(){
        DB::table('konflik_pbph')->where('pbph_info_id', $this->idPbph)->delete();

        $rows = DB::table('konflik')
            ->whereIn('id', array_filter((array) $this->konflikIds))
            ->pluck('id')
            ->map(fn ($konflikId) => [
                'pbph_info_id' => $this->idPbph,
                'konflik_id' => $konflikId,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ])
            ->all();

        if ($rows) {
            DB::table('konflik_pbph')->insert($rows);
        }
    }

    private function authorizeAdmin(){
        if ((int) session('role_id') !== 0) {
            abort(403, 'Akses terbatas untuk administrator.');
        }
    }

    public function render()
    {
        return view('livewire.edit-pbph', [
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
