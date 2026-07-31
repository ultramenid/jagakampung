<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class CmsPbph extends Component
{
    use WithPagination;
    public $deleteName, $deleteID, $deleter;
    public $dataField = 'nama_perusahaan', $dataOrder = 'asc', $paginate = 10, $search = '';

    // ponytail: daftar dibaca dari tabel lokal saja — GeoServer hanya dipanggil di
    // form tambah, supaya GeoServer down tidak mematikan halaman CMS ini.
    public function getPbph(){
        return DB::table('pbph_info')
            ->where(function ($q) {
                $q->where('nama_perusahaan', 'like', '%'.$this->search.'%')
                  ->orWhere('kode_pbph', 'like', '%'.$this->search.'%');
            })
            ->orderBy($this->dataField, $this->dataOrder)
            ->paginate($this->paginate);
    }

    public function sortingField($field){
        $this->dataField = $field;
        $this->dataOrder = $this->dataOrder == 'asc' ? 'desc' : 'asc';
    }

    public function closeDelete(){
        $this->deleter = false;
        $this->deleteName = null;
        $this->deleteID = null;
    }

    public function delete($id){
        $this->authorizeAdmin();

        $dataDelete = DB::table('pbph_info')->where('id', $id)->first();
        if (! $dataDelete) {
            return;
        }
        $this->deleteName = $dataDelete->nama_perusahaan ?: $dataDelete->kode_pbph;
        $this->deleteID = $dataDelete->id;

        $this->deleter = true;
    }

    public function deleting($id){
        $this->authorizeAdmin();

        // file fisik dibuang dulu, kalau tidak baris penunjuknya hilang dan file jadi yatim
        foreach (DB::table('pbph_lampiran')->where('pbph_info_id', $id)->pluck('file') as $file) {
            Storage::disk('public')->delete('pbph-lampiran/'.$file);
        }

        DB::transaction(function () use ($id) {
            DB::table('pbph_lampiran')->where('pbph_info_id', $id)->delete();
            DB::table('pbph_info')->where('id', $id)->delete();
        });

        Toaster::success('Berhasil menghapus info PBPH ' . $this->deleteName);

        $this->closeDelete();
    }

    // Livewire tidak melewati middleware route, jadi peran dicek ulang di sini.
    private function authorizeAdmin(){
        if ((int) session('role_id') !== 0) {
            abort(403, 'Akses terbatas untuk administrator.');
        }
    }

    public function render()
    {
        $databases = $this->getPbph();
        return view('livewire.cms-pbph', compact('databases'));
    }
}
