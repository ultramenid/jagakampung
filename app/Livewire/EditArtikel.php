<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Masmerise\Toaster\Toaster;
use Carbon\Carbon;

class EditArtikel extends Component
{
    public $artikel_id;
    public $konflik_id;
    public $judul_id;
    public $judul_en;
    public $slug;
    public $deskripsi_id;
    public $deskripsi_en;
    public $gambar;
    public $sumber;
    public $tanggal_publish;

    public function mount($id)
    {
        $this->artikel_id = $id;
        $data = DB::table('artikel')->where('id', $this->artikel_id)->first();

        $this->konflik_id = $data->konflik_id;
        $this->judul_id = $data->judul_id;
        $this->judul_en = $data->judul_en;
        $this->slug = $data->slug;
        $this->deskripsi_id = $data->deskripsi_id;
        $this->deskripsi_en = $data->deskripsi_en;
        $this->gambar = $data->gambar;
        $this->sumber = $data->sumber;
        $this->tanggal_publish = $data->tanggal_publish;
    }

    public function storeDatabase()
    {
        if($this->manualValidation()) {
            DB::transaction(function () {
                $judulLama = DB::table('artikel')->where('id', $this->artikel_id)->value('judul_id');
                $gambarLama = DB::table('artikel')->where('id', $this->artikel_id)->value('gambar');

                // update gambar baru jika ada
                if($this->gambar && $this->gambar != $gambarLama){
                    $gambarName = Carbon::now()->timestamp . '_' . $this->gambar->getClientOriginalName();
                    $path = $this->gambar->storeAs('artikels', $gambarName, 'public');
                    $gambarPath = $path;
                    $this->gambar = $gambarPath;
                }else{
                    $this->gambar = DB::table('artikel')->where('id', $this->artikel_id)->value('gambar');
                }

                DB::table('artikel')->where('id', $this->artikel_id)->update([
                    'judul_id' => $this->judul_id,
                    'judul_en' => $this->judul_en,
                    'slug' => \Str::slug($this->judul_id),
                    'deskripsi_id' => $this->deskripsi_id,
                    'deskripsi_en' => $this->deskripsi_en,
                    'gambar' => $gambarPath ?? $gambarLama,
                    'sumber' => $this->sumber,
                    'tanggal_publish' => $this->tanggal_publish,
                    'updated_at' => now(),
                ]);

                 redirect()->to('/cms/konflik');
                
            });
        }
    }

    // delete artikel
    public function deleteArtikel()
    {
        DB::table('artikel')->where('id', $this->artikel_id)->delete();
        redirect()->to('/cms/konflik');
    }

    public function checkNamaExists()
    {
        $check = DB::table('artikel')->where('judul_id', $this->judul_id)->where('id', '!=', $this->artikel_id)->first();
        if($check){
            return true;
        }
        return false;
    }

    public function manualValidation(){
        if($this->judul_id == ''){
            Toaster::error('Judul Indonesia harus diisi!');
            return;
        }elseif($this->judul_en == ''){
            Toaster::error('Judul Inggris harus diisi!');
            return;
        }elseif($this->deskripsi_id == ''){
            Toaster::error('Deskripsi Indonesia harus diisi!');
            return;
        }elseif($this->deskripsi_en == ''){
            Toaster::error('Deskripsi English harus diisi!');
            return;
        }elseif($this->gambar == ''){
            Toaster::error('Gambar harus diisi!');
            return;
        }elseif($this->sumber == ''){
            Toaster::error('Sumber harus diisi!');
            return;
        }elseif($this->tanggal_publish == ''){
            Toaster::error('Tanggal publish harus diisi!');
            return;
        }elseif($this->checkNamaExists()){
            Toaster::error('Nama group sudah ada!');
            return;
        }
        return true;
    }

    
    public function render()
    {
        return view('livewire.edit-artikel');
    }
}
