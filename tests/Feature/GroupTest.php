<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class GroupTest extends TestCase
{
    public function test_can_view_group_list_page(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/group')
            ->assertStatus(200)
            ->assertSee('Group');
    }

    public function test_can_view_tambah_group_page(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/tambah-group')
            ->assertStatus(200)
            ->assertSee('Group');
    }

    public function test_can_create_group(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahGroup::class)
            ->set('nama', 'Group Baru')
            ->set('deskripsi', 'Deskripsi group baru')
            ->call('storeDatabase')
            ->assertRedirect('/cms/group');

        $this->assertDatabaseHas('groups', [
            'nama' => 'Group Baru',
            'deskripsi' => 'Deskripsi group baru',
        ]);
    }

    public function test_create_group_validation_nama_required(): void
    {
        $this->loginAsAdmin();

        $component = Livewire::test(\App\Livewire\TambahGroup::class)
            ->set('nama', '')
            ->set('deskripsi', 'Deskripsi')
            ->call('storeDatabase')
            ->assertNoRedirect();

        $this->assertDatabaseMissing('groups', [
            'deskripsi' => 'Deskripsi',
        ]);
    }

    public function test_create_group_validation_deskripsi_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahGroup::class)
            ->set('nama', 'Nama Group')
            ->set('deskripsi', '')
            ->call('storeDatabase')
            ->assertNoRedirect();

        $this->assertDatabaseMissing('groups', [
            'nama' => 'Nama Group',
        ]);
    }

    public function test_create_group_duplicate_nama(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Existing Group', 'Deskripsi');

        Livewire::test(\App\Livewire\TambahGroup::class)
            ->set('nama', 'Existing Group')
            ->set('deskripsi', 'Deskripsi lain')
            ->call('storeDatabase')
            ->assertNoRedirect();

        $this->assertEquals(
            1,
            DB::table('groups')->where('nama', 'Existing Group')->count()
        );
    }

    public function test_can_view_edit_group_page(): void
    {
        $this->loginAsAdmin();
        $id = $this->seedGroup();

        $this->get("/cms/editgroup/{$id}")
            ->assertStatus(200);
    }

    public function test_can_edit_group(): void
    {
        $this->loginAsAdmin();
        $id = $this->seedGroup('Original Name', 'Original Deskripsi');

        Livewire::test(\App\Livewire\EditGroup::class, ['idDB' => $id])
            ->set('nama', 'Updated Name')
            ->set('deskripsi', 'Updated Deskripsi')
            ->call('storeDatabase')
            ->assertRedirect('/cms/group');

        $this->assertDatabaseHas('groups', [
            'id' => $id,
            'nama' => 'Updated Name',
            'deskripsi' => 'Updated Deskripsi',
        ]);
    }

    public function test_edit_group_cascades_to_konflik(): void
    {
        $this->loginAsAdmin();
        $groupId = $this->seedGroup('Old Group', 'Deskripsi');

        DB::table('konflik')->insert([
            'provinsi' => 'Test',
            'kabkota' => 'Test',
            'kecamatan' => 'Test',
            'desa' => 'Test',
            'lat' => '-7.250',
            'long' => '112.750',
            'luas' => 100,
            'kk' => 50,
            'group' => 'Old Group',
            'perusahaan' => 'Test Perusahaan',
            'status' => 'draft',
            'deskripsikonflik' => 'Test',
            'deskripsiperjuangan' => 'Test',
        ]);

        Livewire::test(\App\Livewire\EditGroup::class, ['idDB' => $groupId])
            ->set('nama', 'New Group')
            ->set('deskripsi', 'Updated')
            ->call('storeDatabase');

        $this->assertDatabaseHas('konflik', [
            'group' => 'New Group',
        ]);

        $this->assertDatabaseMissing('konflik', [
            'group' => 'Old Group',
        ]);
    }

    public function test_edit_group_cascades_to_perusahaans(): void
    {
        $this->loginAsAdmin();
        $groupId = $this->seedGroup('Old Group', 'Deskripsi');

        $this->seedPerusahaan('Old Group', 'Test Corp');

        Livewire::test(\App\Livewire\EditGroup::class, ['idDB' => $groupId])
            ->set('nama', 'New Group')
            ->set('deskripsi', 'Updated')
            ->call('storeDatabase');

        $this->assertDatabaseHas('perusahaans', [
            'group' => 'New Group',
        ]);
    }

    public function test_can_delete_group(): void
    {
        $this->loginAsAdmin();
        $id = $this->seedGroup('To Delete', 'Akan dihapus');

        Livewire::test(\App\Livewire\CmsGroup::class)
            ->call('delete', $id)
            ->assertSet('deleter', true);

        Livewire::test(\App\Livewire\CmsGroup::class)
            ->call('deleting', $id);

        $this->assertDatabaseMissing('groups', ['id' => $id]);
    }

    public function test_can_search_group(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Apple Group');
        $this->seedGroup('Banana Group');
        $this->seedGroup('Cherry Group');

        $component = Livewire::test(\App\Livewire\CmsGroup::class)
            ->set('search', 'Apple');

        $component->assertSee('Apple Group');
        $component->assertDontSee('Banana Group');
    }
}
