<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class CmsInstansi extends Component
{
    use WithPagination;
    public $deleteName, $deleteID, $deleter;
    public $dataField = 'nama', $dataOrder = 'asc', $paginate = 10, $search = '';

    public function sortingField($field){
        $this->dataField = $field;
        $this->dataOrder = $this->dataOrder == 'asc' ? 'desc' : 'asc';
    }

    public function getInstansi(){
        $sc = '%' . $this->search . '%';
        try {
            return  DB::table('instansi')
                        ->select('nama', 'id')
                        ->where('nama', 'like', $sc)
                        ->orderBy($this->dataField, $this->dataOrder)
                        ->paginate($this->paginate);
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function closeDelete(){
        $this->deleter = false;
        $this->deleteName = null;
        $this->deleteID = null;
    }
    public function delete($id){

        //load data to delete function
        $dataDelete = DB::table('instansi')->where('id', $id)->first();
        $this->deleteName = $dataDelete->nama;
        $this->deleteID = $dataDelete->id;

        $this->deleter = true;
    }
    public function deleting($id){
        DB::table('instansi')->where('id', $id)->delete();

        $message = 'Berhasil menghapus group ' . $this->deleteName;
        Toaster::success($message);


        $this->closeDelete();
    }
    public function render()
    {
        $databases = $this->getInstansi();
        return view('livewire.cms-instansi', compact('databases'));
    }
}
