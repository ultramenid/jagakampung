<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class LoginComponent extends Component
{
    public $email, $password;

    public function getDatauser(){
        return DB::table('users')->where('email', $this->email)->where('is_active', 1)->first();
    }

    public function login(){
        // dd($this->getDatauser());
        $this->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        // ponytail: throttle brute-force by email+IP (5 attempts / 60s)
        $key = 'login:' . $this->email . ':' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            Toaster::error('Terlalu banyak percobaan login. Coba lagi sebentar lagi.');
            return;
        }

         //log in logic — single lookup instead of 3×
         $user = $this->getDatauser();
         if($user and Hash::check($this->password, $user->password) and $this->email == $user->email) {
            RateLimiter::clear($key);
            session()->regenerate(); // ponytail: rotate SID on auth to prevent session fixation (CWE-384)
            session([
                'id' => $user->id,
                'role_id'=> $user->role,
                'name' => $user->name,
                'email' => $user->email,
                'yearAlert' => 'all'
            ]);
            redirect('/cms/dashboard');
         }else{
            RateLimiter::hit($key, 60);
            Toaster::error('email & Password not valid.');
         }
    }
    public function render()
    {
        return view('livewire.login-component');
    }
}
