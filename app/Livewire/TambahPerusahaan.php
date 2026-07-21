<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class TambahPerusahaan extends Component
{

    public $group, $perusahaan, $deskripsi;

    public function storeDatabase(){
        if($this->manualValidation()){
            DB::table('perusahaans')->insert([
                'group' => $this->group,
                'perusahaan' => $this->perusahaan,
                'deskripsi' => $this->deskripsi,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);
            redirect()->to('/cms/perusahaan');
        }
    }
    public function checkNamaExists(){
        return DB::table('perusahaans')
            ->where('perusahaan', $this->perusahaan)
            ->exists();
    }
    public function manualValidation(){
        if($this->group == ''){
            Toaster::error('Pilih salah satu group!');
            return;
        }elseif($this->perusahaan == ''){
            Toaster::error('Nama perusahaan harus diisi!');
            return;
        }elseif($this->deskripsi == ''){
            Toaster::error('Deskripsi harus diisi!');
            return;
        }elseif($this->checkNamaExists()){
            Toaster::error('Nama perusahaan sudah ada!');
            return;
        }
        return true;
    }

    public function getGroup(){
        return DB::table('groups')->get();
    }

    public function render(){
        $groups = $this->getGroup();
        return view('livewire.tambah-perusahaan', compact('groups'));
    }
}
