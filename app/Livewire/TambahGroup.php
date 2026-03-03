<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class TambahGroup extends Component
{
    public $nama, $deskripsi;

    public function storeDatabase(){
        if($this->manualValidation()){
            DB::table('groups')->insert([
                'nama' => $this->nama,
                'deskripsi' => $this->deskripsi,
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);
            redirect()->to('/cms/group');
        }
    }
    public function checkNamaExists(){
        $check = DB::table('groups')->where('nama', $this->nama)->first();
        if($check){
            return true;
        }
        return false;
    }

    public function manualValidation(){
        if($this->nama == ''){
            Toaster::error('Nama harus diisi!');
            return;
        }elseif($this->deskripsi == ''){
            Toaster::error('Deskripsi harus diisi!');
            return;
        }elseif($this->checkNamaExists()){
            Toaster::error('Nama group sudah ada!');
            return;
        }
        return true;
    }

    public function render(){
        return view('livewire.tambah-group');
    }
}
