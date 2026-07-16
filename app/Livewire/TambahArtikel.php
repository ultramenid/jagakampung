<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\{Component, WithFileUploads};
use Masmerise\Toaster\Toaster;

class TambahArtikel extends Component
{
    use WithFileUploads;
    public $konflik_id;
    public $judul_id;
    public $judul_en = 'judul en';
    public $slug;
    public $deskripsi_id;
    public $deskripsi_en = 'desk en';
    public $gambar;
    public $sumber;
    public $tanggal_publish;
    public $status;

    // ambil konflik_id
    public function mount($id)
    {
        $this->konflik_id = $id;
        $this->status = 'draft';
    }

    // simpan ke database 
    public function storeDatabase()
    {
        // ponytail: validate image upload (only image mimes allowed)
        $this->validate([
            'gambar' => 'required|image|mimes:jpg,jpeg,png,webp',
        ]);

        // ponytail: only attach articles to a konflik you own (or admin)
        $konflik = DB::table('konflik')->where('id', $this->konflik_id)->first();
        if (! $konflik || ((int) session('role_id') !== 0 && (int) ($konflik->user_id ?? 0) !== (int) session('id'))) {
            abort(403, 'Anda tidak berhak menambah artikel ke konflik ini.');
        }

        // // ambil konfilik_id
        // $konflik = DB::table('konflik')->where('id', $this->konflik_id)->first();
        // if(!$konflik){
        //     Toaster::error('Konflik tidak ditemukan!');
        //     return;
        // }

        // upload gambar
        $gambarName =  null;
        if($this->gambar){
            $gambarName = Carbon::now()->timestamp . '_' . $this->gambar->getClientOriginalName();
            $path = $this->gambar->storeAs('artikels', $gambarName, 'public');
            $gambarPath = $path;
        }

        if($this->manualValidation()) {
            $status = in_array($this->status, ['draft', 'publish'], true) ? $this->status : 'draft';
            if ((int) session('role_id') !== 0) {
                $status = 'draft';
            }
            DB::table('artikel')->insert([
                'konflik_id' => $this->konflik_id,
                'judul_id' => $this->judul_id,
                'judul_en' => $this->judul_en ?? 'judul_en',
                'slug' => \Str::slug($this->judul_id),
                'deskripsi_id' => $this->deskripsi_id,
                'deskripsi_en' => $this->deskripsi_en ?? 'desk_en',
                'gambar' => $gambarPath,
                'sumber' => $this->sumber,
                'status' => $status,
                'tanggal_publish' => Carbon::parse($this->tanggal_publish)->format('Y-m-d'),
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);
             redirect()->to('/cms/konflik');
        }
    }

    public function checkNamaExists()
    {
        $check = DB::table('artikel')->where('judul_id', $this->judul_id)->first();
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
            Toaster::error('Judul artikel sudah ada!');
            return;
        }
        return true;
    }

    public function render()
    {
        return view('livewire.tambah-artikel');
    }
}
