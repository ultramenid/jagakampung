<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class CmsUsers extends Component
{
    use WithPagination;
    public $deleteName, $deleteID, $deleter;
    public $dataField = 'name', $dataOrder = 'asc', $paginate = 10, $search = '';

    public function getUsers(){
        return DB::table('users')
            ->where('name', 'like', '%'.$this->search.'%')
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

        //load data to delete function
        $dataDelete = DB::table('users')->where('id', $id)->first();
        $this->deleteName = $dataDelete->name;
        $this->deleteID = $dataDelete->id;

        $this->deleter = true;
    }
    public function deleting($id){
        if ((int) session('role_id') !== 0) {
            abort(403, 'Akses terbatas untuk administrator.');
        }
        if ((int) $id === (int) session('id')) {
            Toaster::error('Tidak dapat menghapus akun sendiri.');
            $this->closeDelete();
            return;
        }
        DB::table('users')->where('id', $id)->delete();

        $message = 'Berhasil menghapus user ' . $this->deleteName;
        Toaster::success($message);


        $this->closeDelete();
    }

    public function render()
    {
        $databases = $this->getUsers();
        return view('livewire.cms-users', compact('databases'));
    }
}
