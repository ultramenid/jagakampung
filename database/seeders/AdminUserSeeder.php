<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@jagakampung.com',
            'password' => bcrypt('password'),
            'instansi' => 'JagaKampung',
            'role' => 1,
            'is_active' => 1,
        ]);
    }
}
