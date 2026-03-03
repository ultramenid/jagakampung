<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class EditGroup extends Component
{

    public $idGroup, $nama, $deskripsi;

    public function mount($idDB)
    {
        $this->idGroup = $idDB;
        $data = DB::table('groups')->where('id', $this->idGroup)->first();
        $this->nama = $data->nama;
        $this->deskripsi = $data->deskripsi;
    }


    public function storeDatabase(){
        if($this->manualValidation()){
           DB::transaction(function () {
                $namaLama = DB::table('groups')->where('id', $this->idGroup)->value('nama');

                DB::table('groups')->where('id', $this->idGroup)->update([
                    'nama' => $this->nama,
                    'deskripsi' => $this->deskripsi,
                    'updated_at' => Carbon::now('Asia/Jakarta'),
                ]);

                DB::table('konflik')->where('group', $namaLama)->update([
                    'group' => $this->nama,
                    'updated_at' => Carbon::now('Asia/Jakarta')
                ]);

                DB::table('perusahaans')->where('group', $namaLama)->update([
                    'group' => $this->nama,
                    'updated_at' => Carbon::now('Asia/Jakarta')
                ]);
            });


            redirect()->to('/cms/group');
        }
    }


     public function manualValidation(){
        if($this->nama == ''){
            Toaster::error('Nama harus diisi!');
            return;
        }elseif($this->deskripsi == ''){
            Toaster::error('Deskripsi harus diisi!');
            return;
        }
        return true;
    }
    public function render()
    {
        return view('livewire.edit-group');
    }
}
