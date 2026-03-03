<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class CmsKonflik extends Component
{
    use WithPagination;
    public $deleteName, $deleteID, $deleter;
    public $dataField = 'nama', $dataOrder = 'asc', $paginate = 10, $search = '';






    public function render()
    {
        $databases = [];
        return view('livewire.cms-konflik', compact('databases'));
    }
}
