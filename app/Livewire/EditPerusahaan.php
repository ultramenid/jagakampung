<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class EditPerusahaan extends Component
{
    public $idPerusahaan, $perusahaan, $group, $deskripsi;

    public function mount($idDB){
        $this->idPerusahaan = $idDB;
        $data = DB::table('perusahaans')->where('id', $this->idPerusahaan)->first();
        $this->perusahaan = $data->perusahaan;
        $this->group = $data->group;
        $this->deskripsi = $data->deskripsi;
    }

    public function storeDatabase(){
        if ((int) session('role_id') !== 0) {
            abort(403, 'Akses terbatas untuk administrator.');
        }
        if($this->manualValidation()){

            DB::transaction(function () {
                $namaLama = DB::table('perusahaans')->where('id', $this->idPerusahaan)->value('perusahaan');

                DB::table('perusahaans')->where('id', $this->idPerusahaan)->update([
                    'perusahaan' => $this->perusahaan,
                    'group' => $this->group,
                    'deskripsi' => $this->deskripsi,
                    'updated_at' => Carbon::now('Asia/Jakarta'),
                ]);

                DB::table('konflik')->where('perusahaan', $namaLama)
                ->update([
                    'perusahaan' => $this->perusahaan,
                    'updated_at' => Carbon::now('Asia/Jakarta')
                ]);


                redirect()->to('/cms/perusahaan');

            });
        }

    }

    public function manualValidation(){
        if($this->group == ''){
            Toaster::error('Pilih salah satu group!');
            return;
        }elseif($this->deskripsi == ''){
            Toaster::error('Deskripsi harus diisi!');
            return;
        }elseif($this->perusahaan == ''){
            Toaster::error('Nama perusahaan harus diisi!');
            return;
        }
        return true;
    }

    public function getGroup(){
        return DB::table('groups')->get();
    }

    public function render()
    {
        $groups = $this->getGroup();
        return view('livewire.edit-perusahaan', compact('groups'));
    }
}
