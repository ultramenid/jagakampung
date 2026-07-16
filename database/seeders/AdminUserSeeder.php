<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = \Illuminate\Support\Str::random(32);
        User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@jagakampung.com',
            'password' => bcrypt($password),
            'instansi' => 'JagaKampung',
            'role' => 0,
            'is_active' => 1,
        ]);
        dump("Initial admin password: {$password}");
    }
}
