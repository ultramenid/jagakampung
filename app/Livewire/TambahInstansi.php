<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class TambahInstansi extends Component
{
    public $nama;

    public function storeDatabase(){
        if (! session('id') || (int) session('role_id') !== 0) {
            abort(403, 'Akses terbatas untuk administrator.');
        }
        if($this->manualValidation()){
            DB::table('instansi')->insert([
                'nama' => $this->nama,
            ]);
            redirect()->to('/cms/instansi');
        }
    }

    public function manualValidation(){
        if($this->nama == ''){
            Toaster::error('Nama harus diisi!');
            return;
        }elseif($this->checkNamaExists()){
            Toaster::error('Nama lembaga sudah ada!');
            return;
        }
        return true;
    }

    public function checkNamaExists(){
        return DB::table('instansi')
            ->where('nama', $this->nama)
            ->exists();
    }

    public function render()
    {
        return view('livewire.tambah-instansi');
    }
}
