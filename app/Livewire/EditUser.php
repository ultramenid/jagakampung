<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EditUser extends Component
{
    public $idUser, $name, $email, $password, $role, $instansi;

    public function mount($idDB){
        $this->idUser = $idDB;
        $user = DB::table('users')->where('id', $this->idUser)->first();
        $this->name = $user->name;
        $this->instansi = $user->instansi;
        $this->email = $user->email;
        $this->role = $user->role_id;

    }

    public function render()
    {
        return view('livewire.edit-user');
    }
}
