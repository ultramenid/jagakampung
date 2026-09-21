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

        $isAdmin = session('id') && (int) session('role_id') === 0;
        if (! $isAdmin) {
            $query->where(function ($q) {
                $q->where('status', '!=', 'draft');
                if (session('id')) {
                    $q->orWhere('user_id', session('id'));
                }
            });
        }

        if ($this->search) {
            // ILIKE (Postgres) vs LIKE (MySQL) — MySQL's default collation is case-insensitive.
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($like, $term) {
                $q->where('desa', $like, $term)
                  ->orWhere('kecamatan', $like, $term)
                  ->orWhere('kabkota', $like, $term)
                  ->orWhere('provinsi', $like, $term)
                  ->orWhere('nama_kelompok', $like, $term)
                  ->orWhere('group', $like, $term)
                  ->orWhere('perusahaan', $like, $term);
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $databases = $query->paginate(15);

        return view('livewire.cms-konflik', compact('databases'));
    }
}
