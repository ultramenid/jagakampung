<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class EditInstansi extends Component
{
    public $idInstansi, $nama;

    public function mount($idDB){
        $this->idInstansi = $idDB;
        $data = DB::table('instansi')->where('id', $this->idInstansi)->first();
        $this->nama = $data->nama;
    }

    public function storeDatabase(){
        if ((int) session('role_id') !== 0) {
            abort(403, 'Akses terbatas untuk administrator.');
        }
        if($this->manualValidation()){
            DB::transaction(function () {
                $namaLama = DB::table('instansi')->where('id', $this->idInstansi)->value('nama');

                DB::table('instansi')->where('id', $this->idInstansi)->update([
                    'nama' => $this->nama,
                ]);

                DB::table('konflik_lembaga')->where('nama', $namaLama)
                ->update([
                    'nama' => $this->nama
                ]);
            });
            redirect()->to('/cms/instansi');
        }
    }

    public function manualValidation(){
        if($this->nama == ''){
            Toaster::error('Nama harus diisi!');
            return;
        }
        return true;
    }

    public function render()
    {
        return view('livewire.edit-instansi');
    }
}
