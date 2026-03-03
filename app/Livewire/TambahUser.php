<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class TambahUser extends Component
{
    public $nama, $email, $password, $role, $instansi;

    public function checkUser(){
        $check = DB::table('users')->where('email', $this->email)->first();
        if($check){
            return true;
        }
        return false;
    }

    public function storeDatabase(){
        if($this->manualValidation()){
            DB::table('users')->insert([
                'name' => $this->nama,
                'email' => $this->email,
                'instansi' => $this->instansi,
                'role_id' => $this->role,
                'is_active' => 1,
                'password' => Hash::make($this->password),
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);
            redirect()->to('/cms/users');
        }
    }

    public function manualValidation(){
        if($this->nama == ''){
            Toaster::error('Nama harus diisi!');
            return;
        }elseif($this->email == ''){
            Toaster::error('Email harus diisi!');
            return;
        }elseif($this->checkUser()){
            Toaster::error('Data sudah ada!');
            return;
        }elseif($this->instansi == ''){
            Toaster::error('Nama instansi harus diisi!');
            return;
        }elseif($this->role == ''){
            Toaster::error('Role harus diisi!');
            return;
        }elseif($this->password == ''){
            Toaster::error('Password harus diisi!');
            return;
        }
        return true;
    }

    public function render()
    {
        return view('livewire.tambah-user');
    }
}
