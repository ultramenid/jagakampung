<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CmsKonflik extends Component
{
    use WithPagination;

    public $deleteName, $deleteID, $deleter;
    public $viewMode = 'map';
    public $search = '';
    public $filterStatus = '';
    protected $queryString = ['viewMode', 'search', 'filterStatus'];

    public function render()
    {
        $query = DB::table('konflik')
            ->select('konflik.*')
            ->orderByDesc('konflik.id');

        if ((int) session('role_id') !== 0) {
            $query->where(function ($q) {
                $q->where('status', '!=', 'draft')
                  ->orWhere('user_id', session('id'));
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('desa', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('kecamatan', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('kabkota', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('provinsi', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('group', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('perusahaan', 'ILIKE', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $databases = $query->paginate(15);

        return view('livewire.cms-konflik', compact('databases'));
    }
}
