<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh', ['--database' => 'sqlite']);
    }

    protected function loginAsAdmin(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'instansi' => 'TestInstansi',
            'role' => 0,
            'is_active' => 1,
        ]);

        session([
            'id' => $userId,
            'role_id' => 0,
            'name' => 'Admin',
            'email' => 'admin@test.com',
        ]);
    }

    protected function loginAsUser(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'instansi' => 'TestInstansi',
            'role' => 1,
            'is_active' => 1,
        ]);

        session([
            'id' => $userId,
            'role_id' => 1,
            'name' => 'Regular User',
            'email' => 'user@test.com',
        ]);
    }

    protected function seedGroup(string $nama = 'Test Group', string $deskripsi = 'Test Deskripsi'): int
    {
        return DB::table('groups')->insertGetId([
            'nama' => $nama,
            'deskripsi' => $deskripsi,
        ]);
    }

    protected function seedPerusahaan(string $group, string $perusahaan = 'Test Perusahaan', string $deskripsi = 'Test Deskripsi'): int
    {
        return DB::table('perusahaans')->insertGetId([
            'group' => $group,
            'perusahaan' => $perusahaan,
            'deskripsi' => $deskripsi,
        ]);
    }

    protected function seedInstansi(string $nama = 'Test Instansi'): int
    {
        return DB::table('instansi')->insertGetId([
            'nama' => $nama,
        ]);
    }

    protected function seedKonflik(array $overrides = []): int
    {
        $defaults = [
            'provinsi' => 'Test Provinsi',
            'kabkota' => 'Test Kabkota',
            'kecamatan' => 'Test Kecamatan',
            'desa' => 'Test Desa',
            'lat' => '-7.250',
            'long' => '112.750',
            'luas' => 100,
            'kk' => 50,
            'group' => 'Test Group',
            'perusahaan' => 'Test Perusahaan',
            'status' => 'draft',
            'deskripsikonflik' => 'Test deskripsi konflik',
            'deskripsiperjuangan' => 'Test deskripsi perjuangan',
            'user_id' => session('id'),
        ];

        $data = array_merge($defaults, $overrides);

        return DB::table('konflik')->insertGetId($data);
    }
}
