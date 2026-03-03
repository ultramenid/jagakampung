<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class CmsPerusahaan extends Component
{
    use WithPagination;
    public $deleteName, $deleteID, $deleter;
    public $dataField = 'perusahaan', $dataOrder = 'asc', $paginate = 10, $search = '';


    public function getPerusahaan(){
        $query = DB::table('perusahaans')
            ->where('perusahaan', 'like', '%'.$this->search.'%')
            ->orderBy($this->dataField, $this->dataOrder);
        $databases = $query->paginate($this->paginate);
        return $databases;
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

        //load data to delete function
        $dataDelete = DB::table('perusahaans')->where('id', $id)->first();
        $this->deleteName = $dataDelete->perusahaan;
        $this->deleteID = $dataDelete->id;

        $this->deleter = true;
    }
    public function deleting($id){
        DB::table('perusahaans')->where('id', $id)->delete();

        $message = 'Berhasil menghapus group ' . $this->deleteName;
        Toaster::success($message);


        $this->closeDelete();
    }
    public function render()
    {
        $databases = $this->getPerusahaan();
        return view('livewire.cms-perusahaan', compact('databases'));
    }
}
