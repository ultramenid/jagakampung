<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PerusahaanTest extends TestCase
{
    public function test_can_view_perusahaan_list_page(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/perusahaan')
            ->assertStatus(200)
            ->assertSee('Perusahaan');
    }

    public function test_can_view_tambah_perusahaan_page(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/tambah-perusahaan')
            ->assertStatus(200);
    }

    public function test_can_create_perusahaan(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');

        Livewire::test(\App\Livewire\TambahPerusahaan::class)
            ->set('group', 'Test Group')
            ->set('perusahaan', 'PT Maju Jaya')
            ->set('deskripsi', 'Perusahaan bergerak di bidang pertanian')
            ->call('storeDatabase')
            ->assertRedirect('/cms/perusahaan');

        $this->assertDatabaseHas('perusahaans', [
            'group' => 'Test Group',
            'perusahaan' => 'PT Maju Jaya',
        ]);
    }

    public function test_create_perusahaan_validation_group_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahPerusahaan::class)
            ->set('group', '')
            ->set('perusahaan', 'PT Maju')
            ->set('deskripsi', 'Deskripsi')
            ->call('storeDatabase')
            ->assertNoRedirect();

        $this->assertDatabaseMissing('perusahaans', [
            'perusahaan' => 'PT Maju',
        ]);
    }

    public function test_create_perusahaan_validation_perusahaan_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahPerusahaan::class)
            ->set('group', 'Test Group')
            ->set('perusahaan', '')
            ->set('deskripsi', 'Deskripsi')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_create_perusahaan_validation_deskripsi_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahPerusahaan::class)
            ->set('group', 'Test Group')
            ->set('perusahaan', 'PT Maju')
            ->set('deskripsi', '')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_create_perusahaan_duplicate(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        DB::table('perusahaans')->insert([
            'group' => 'Test Group',
            'perusahaan' => 'PT Duplicate',
            'deskripsi' => 'Existing',
        ]);

        Livewire::test(\App\Livewire\TambahPerusahaan::class)
            ->set('group', 'Test Group')
            ->set('perusahaan', 'PT Duplicate')
            ->set('deskripsi', 'Another')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_can_edit_perusahaan(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $id = $this->seedPerusahaan('Test Group', 'PT Lama', 'Deskripsi lama');

        Livewire::test(\App\Livewire\EditPerusahaan::class, ['idDB' => $id])
            ->set('perusahaan', 'PT Baru')
            ->set('group', 'Test Group')
            ->set('deskripsi', 'Deskripsi baru')
            ->call('storeDatabase')
            ->assertRedirect('/cms/perusahaan');

        $this->assertDatabaseHas('perusahaans', [
            'id' => $id,
            'perusahaan' => 'PT Baru',
        ]);
    }

    public function test_edit_perusahaan_cascades_to_konflik(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $id = $this->seedPerusahaan('Test Group', 'PT Lama Corp', 'Deskripsi');

        DB::table('konflik')->insert([
            'provinsi' => 'Test',
            'kabkota' => 'Test',
            'kecamatan' => 'Test',
            'desa' => 'Test',
            'lat' => '-7.250',
            'long' => '112.750',
            'luas' => 100,
            'kk' => 50,
            'group' => 'Test Group',
            'perusahaan' => 'PT Lama Corp',
            'status' => 'draft',
            'deskripsikonflik' => 'Test',
            'deskripsiperjuangan' => 'Test',
        ]);

        Livewire::test(\App\Livewire\EditPerusahaan::class, ['idDB' => $id])
            ->set('perusahaan', 'PT Baru Corp')
            ->set('group', 'Test Group')
            ->set('deskripsi', 'Updated')
            ->call('storeDatabase');

        $this->assertDatabaseHas('konflik', [
            'perusahaan' => 'PT Baru Corp',
        ]);
    }

    public function test_can_delete_perusahaan(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $id = $this->seedPerusahaan('Test Group', 'To Delete');

        Livewire::test(\App\Livewire\CmsPerusahaan::class)
            ->call('delete', $id)
            ->assertSet('deleter', true);

        Livewire::test(\App\Livewire\CmsPerusahaan::class)
            ->call('deleting', $id);

        $this->assertDatabaseMissing('perusahaans', ['id' => $id]);
    }

    public function test_can_search_perusahaan(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Group A');
        $this->seedPerusahaan('Group A', 'PT Alpha');
        $this->seedPerusahaan('Group A', 'PT Beta');

        $component = Livewire::test(\App\Livewire\CmsPerusahaan::class)
            ->set('search', 'Alpha');

        $component->assertSee('PT Alpha');
        $component->assertDontSee('PT Beta');
    }
}
