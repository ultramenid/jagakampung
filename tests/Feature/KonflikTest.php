<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class KonflikTest extends TestCase
{
    public function test_can_view_konflik_list_page(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/konflik')
            ->assertStatus(200);
    }

    public function test_can_view_tambah_konflik_page(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/tambah-konflik')
            ->assertStatus(200);
    }

    public function test_admin_can_create_konflik_with_status_aktif(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('provinsi', 'Jawa Timur')
            ->set('kabkota', 'Surabaya')
            ->set('kecamatan', 'Tegalsari')
            ->set('desa', 'Kedungdoro')
            ->set('latitude', '-7.260')
            ->set('longtitude', '112.740')
            ->set('luas', 150)
            ->set('kk', 75)
            ->set('selectedStatus', 'aktif')
            ->set('selectedGroup', 'Test Group')
            ->set('selectedPerusahaan', 'Test Perusahaan')
            ->set('deskripsikonflik', 'Konflik lahan pertanian')
            ->set('deskripsiperjuangan', 'Perjuangan masyarakat adat')
            ->call('storeDatabase')
            ->assertRedirect('/cms/konflik');

        $this->assertDatabaseHas('konflik', [
            'provinsi' => 'Jawa Timur',
            'kabkota' => 'Surabaya',
            'status' => 'aktif',
            'group' => 'Test Group',
            'perusahaan' => 'Test Perusahaan',
        ]);
    }

    public function test_admin_can_create_konflik_with_status_potensi(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('provinsi', 'Jawa Timur')
            ->set('kabkota', 'Banyuwangi')
            ->set('kecamatan', 'Kalipuro')
            ->set('desa', 'Kelir')
            ->set('latitude', '-8.200')
            ->set('longtitude', '114.350')
            ->set('luas', 200)
            ->set('kk', 100)
            ->set('selectedStatus', 'potensi')
            ->set('selectedGroup', 'Test Group')
            ->set('selectedPerusahaan', 'Test Perusahaan')
            ->set('deskripsikonflik', 'Potensi konflik tambang')
            ->set('deskripsiperjuangan', 'Warga menolak tambang')
            ->call('storeDatabase')
            ->assertRedirect('/cms/konflik');

        $this->assertDatabaseHas('konflik', [
            'status' => 'potensi',
            'kabkota' => 'Banyuwangi',
        ]);
    }

    public function test_regular_user_konflik_forced_to_draft(): void
    {
        $this->loginAsUser();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('provinsi', 'Jawa Timur')
            ->set('kabkota', 'Malang')
            ->set('kecamatan', 'Batu')
            ->set('desa', 'Tlekung')
            ->set('latitude', '-7.870')
            ->set('longtitude', '112.520')
            ->set('luas', 50)
            ->set('kk', 25)
            ->set('selectedGroup', 'Test Group')
            ->set('selectedPerusahaan', 'Test Perusahaan')
            ->set('deskripsikonflik', 'Konflik warga')
            ->set('deskripsiperjuangan', 'Warga demo')
            ->call('storeDatabase')
            ->assertRedirect('/cms/konflik');

        $this->assertDatabaseHas('konflik', [
            'status' => 'draft',
            'user_id' => session('id'),
        ]);
    }

    public function test_konflik_validation_wilayah_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('desa', '')
            ->set('latitude', '-7.250')
            ->set('longtitude', '112.750')
            ->set('luas', 100)
            ->set('kk', 50)
            ->set('selectedStatus', 'aktif')
            ->set('selectedGroup', 'Group')
            ->set('selectedPerusahaan', 'Perusahaan')
            ->set('deskripsikonflik', 'Test')
            ->set('deskripsiperjuangan', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_konflik_validation_koordinat_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('desa', 'Test Desa')
            ->set('latitude', '')
            ->set('longtitude', '')
            ->set('luas', 100)
            ->set('kk', 50)
            ->set('selectedStatus', 'aktif')
            ->set('selectedGroup', 'Group')
            ->set('selectedPerusahaan', 'Perusahaan')
            ->set('deskripsikonflik', 'Test')
            ->set('deskripsiperjuangan', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_konflik_validation_luas_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('desa', 'Test Desa')
            ->set('latitude', '-7.250')
            ->set('longtitude', '112.750')
            ->set('luas', '')
            ->set('kk', 50)
            ->set('selectedStatus', 'aktif')
            ->set('selectedGroup', 'Group')
            ->set('selectedPerusahaan', 'Perusahaan')
            ->set('deskripsikonflik', 'Test')
            ->set('deskripsiperjuangan', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_konflik_validation_kk_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('desa', 'Test Desa')
            ->set('latitude', '-7.250')
            ->set('longtitude', '112.750')
            ->set('luas', 100)
            ->set('kk', '')
            ->set('selectedStatus', 'aktif')
            ->set('selectedGroup', 'Group')
            ->set('selectedPerusahaan', 'Perusahaan')
            ->set('deskripsikonflik', 'Test')
            ->set('deskripsiperjuangan', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_konflik_validation_status_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('desa', 'Test Desa')
            ->set('latitude', '-7.250')
            ->set('longtitude', '112.750')
            ->set('luas', 100)
            ->set('kk', 50)
            ->set('selectedStatus', '')
            ->set('selectedGroup', 'Group')
            ->set('selectedPerusahaan', 'Perusahaan')
            ->set('deskripsikonflik', 'Test')
            ->set('deskripsiperjuangan', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_konflik_validation_deskripsi_konflik_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('desa', 'Test Desa')
            ->set('latitude', '-7.250')
            ->set('longtitude', '112.750')
            ->set('luas', 100)
            ->set('kk', 50)
            ->set('selectedStatus', 'aktif')
            ->set('selectedGroup', 'Group')
            ->set('selectedPerusahaan', 'Perusahaan')
            ->set('deskripsikonflik', '')
            ->set('deskripsiperjuangan', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_konflik_validation_deskripsi_perjuangan_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('desa', 'Test Desa')
            ->set('latitude', '-7.250')
            ->set('longtitude', '112.750')
            ->set('luas', 100)
            ->set('kk', 50)
            ->set('selectedStatus', 'aktif')
            ->set('selectedGroup', 'Group')
            ->set('selectedPerusahaan', 'Perusahaan')
            ->set('deskripsikonflik', 'Test')
            ->set('deskripsiperjuangan', '')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_konflik_validation_group_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('desa', 'Test Desa')
            ->set('latitude', '-7.250')
            ->set('longtitude', '112.750')
            ->set('luas', 100)
            ->set('kk', 50)
            ->set('selectedStatus', 'aktif')
            ->set('selectedGroup', '')
            ->set('selectedPerusahaan', 'Perusahaan')
            ->set('deskripsikonflik', 'Test')
            ->set('deskripsiperjuangan', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_konflik_validation_perusahaan_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('desa', 'Test Desa')
            ->set('latitude', '-7.250')
            ->set('longtitude', '112.750')
            ->set('luas', 100)
            ->set('kk', 50)
            ->set('selectedStatus', 'aktif')
            ->set('selectedGroup', 'Group')
            ->set('selectedPerusahaan', '')
            ->set('deskripsikonflik', 'Test')
            ->set('deskripsiperjuangan', 'Test')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_konflik_stores_user_id(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');
        $userId = session('id');

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('provinsi', 'Jawa Timur')
            ->set('kabkota', 'Surabaya')
            ->set('kecamatan', 'Tegalsari')
            ->set('desa', 'Kedungdoro')
            ->set('latitude', '-7.260')
            ->set('longtitude', '112.740')
            ->set('luas', 150)
            ->set('kk', 75)
            ->set('selectedStatus', 'aktif')
            ->set('selectedGroup', 'Test Group')
            ->set('selectedPerusahaan', 'Test Perusahaan')
            ->set('deskripsikonflik', 'Testing user ID')
            ->set('deskripsiperjuangan', 'Testing user ID')
            ->call('storeDatabase')
            ->assertRedirect('/cms/konflik');

        $this->assertDatabaseHas('konflik', [
            'deskripsikonflik' => 'Testing user ID',
            'user_id' => $userId,
        ]);
    }

    public function test_konflik_can_be_created_with_lembaga(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');
        $this->seedInstansi('BPN');

        Livewire::test(\App\Livewire\TambahKonflik::class)
            ->set('provinsi', 'Jawa Timur')
            ->set('kabkota', 'Surabaya')
            ->set('kecamatan', 'Tegalsari')
            ->set('desa', 'Kedungdoro')
            ->set('latitude', '-7.260')
            ->set('longtitude', '112.740')
            ->set('luas', 150)
            ->set('kk', 75)
            ->set('selectedStatus', 'aktif')
            ->set('selectedGroup', 'Test Group')
            ->set('selectedPerusahaan', 'Test Perusahaan')
            ->set('deskripsikonflik', 'Dengan lembaga')
            ->set('deskripsiperjuangan', 'Dengan lembaga')
            ->call('setLembaga', 'BPN')
            ->call('storeDatabase')
            ->assertRedirect('/cms/konflik');

        $konflik = DB::table('konflik')->where('deskripsikonflik', 'Dengan lembaga')->first();

        $this->assertNotNull($konflik);

        $this->assertDatabaseHas('konflik_lembaga', [
            'konflik_id' => $konflik->id,
            'nama' => 'BPN',
        ]);
    }
}
