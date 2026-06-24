<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class InstansiTest extends TestCase
{
    public function test_can_view_instansi_list_page(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/instansi')
            ->assertStatus(200)
            ->assertSee('Instansi');
    }

    public function test_can_view_tambah_instansi_page(): void
    {
        $this->loginAsAdmin();

        $this->get('/cms/tambah-instansi')
            ->assertStatus(200);
    }

    public function test_can_create_instansi(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahInstansi::class)
            ->set('nama', 'Kementerian Pertanian')
            ->call('storeDatabase')
            ->assertRedirect('/cms/instansi');

        $this->assertDatabaseHas('instansi', [
            'nama' => 'Kementerian Pertanian',
        ]);
    }

    public function test_create_instansi_validation_nama_required(): void
    {
        $this->loginAsAdmin();

        Livewire::test(\App\Livewire\TambahInstansi::class)
            ->set('nama', '')
            ->call('storeDatabase')
            ->assertNoRedirect();

        $this->assertDatabaseCount('instansi', 0);
    }

    public function test_can_edit_instansi(): void
    {
        $this->loginAsAdmin();
        $id = $this->seedInstansi('Lembaga Lama');

        Livewire::test(\App\Livewire\EditInstansi::class, ['idDB' => $id])
            ->set('nama', 'Lembaga Baru')
            ->call('storeDatabase')
            ->assertRedirect('/cms/instansi');

        $this->assertDatabaseHas('instansi', [
            'id' => $id,
            'nama' => 'Lembaga Baru',
        ]);
    }

    public function test_edit_instansi_cascades_to_konflik_lembaga(): void
    {
        $this->loginAsAdmin();
        $id = $this->seedInstansi('Lembaga A');

        DB::table('konflik_lembaga')->insert([
            'konflik_id' => 1,
            'nama' => 'Lembaga A',
        ]);

        Livewire::test(\App\Livewire\EditInstansi::class, ['idDB' => $id])
            ->set('nama', 'Lembaga B')
            ->call('storeDatabase');

        $this->assertDatabaseHas('konflik_lembaga', [
            'nama' => 'Lembaga B',
        ]);
    }

    public function test_can_delete_instansi(): void
    {
        $this->loginAsAdmin();
        $id = $this->seedInstansi('To Delete');

        Livewire::test(\App\Livewire\CmsInstansi::class)
            ->call('delete', $id)
            ->assertSet('deleter', true);

        Livewire::test(\App\Livewire\CmsInstansi::class)
            ->call('deleting', $id);

        $this->assertDatabaseMissing('instansi', ['id' => $id]);
    }

    public function test_can_search_instansi(): void
    {
        $this->loginAsAdmin();
        $this->seedInstansi('Badan Pertanahan');
        $this->seedInstansi('Kementerian Kehutanan');
        $this->seedInstansi('Dinas PU');

        $component = Livewire::test(\App\Livewire\CmsInstansi::class)
            ->set('search', 'Pertanahan');

        $component->assertSee('Badan Pertanahan');
        $component->assertDontSee('Kementerian Kehutanan');
    }
}
