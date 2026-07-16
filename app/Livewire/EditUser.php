<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class EditUser extends Component
{
    public $idUser, $name, $email, $password, $role, $instansi;

    public function mount($idDB){
        if ((int) session('role_id') !== 0) {
            abort(403, 'Akses terbatas untuk administrator.');
        }
        $this->idUser = $idDB;
        $user = DB::table('users')->where('id', $this->idUser)->first();
        $this->name = $user->name;
        $this->instansi = $user->instansi;
        $this->email = $user->email;
        $this->role = $user->role;
    }

    public function storeDatabase(){
        if ((int) session('role_id') !== 0) {
            abort(403, 'Akses terbatas untuk administrator.');
        }
        if($this->manualValidation()){
            $updateData = [
                'name' => $this->name,
                'email' => $this->email,
                'instansi' => $this->instansi,
                'role' => in_array((string) $this->role, ['0', '1'], true) ? (int) $this->role : 1, // ponytail: whitelist role (cast to string: mount loads int from DB, select sends string), default to User
            ];

            if($this->password){
                $updateData['password'] = Hash::make($this->password);
            }

            DB::table('users')->where('id', $this->idUser)->update($updateData);

            redirect()->to('/cms/users');
        }
    }

    public function checkUser(){
        $check = DB::table('users')
            ->where('email', $this->email)
            ->where('id', '!=', $this->idUser)
            ->first();
        if($check){
            return true;
        }
        return false;
    }

    public function manualValidation(){
        if($this->name == ''){
            Toaster::error('Nama harus diisi!');
            return;
        }elseif($this->email == ''){
            Toaster::error('Email harus diisi!');
            return;
        }elseif($this->checkUser()){
            Toaster::error('Email sudah digunakan!');
            return;
        }elseif($this->instansi == ''){
            Toaster::error('Nama instansi harus diisi!');
            return;
        }elseif($this->role == ''){
            Toaster::error('Role harus diisi!');
            return;
        }
        return true;
    }

    public function render()
    {
        return view('livewire.edit-user');
    }
}
