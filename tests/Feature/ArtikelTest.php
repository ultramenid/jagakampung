<?php

namespace Tests\Feature;

use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ArtikelTest extends TestCase
{
    private function makeImage(): File
    {
        return File::image('artikel.jpg', 100, 100);
    }

    public function test_can_view_tambah_artikel_page(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');
        $konflikId = $this->seedKonflik();

        $this->get("/cms/tambah-artikel/{$konflikId}")
            ->assertStatus(200);
    }

    public function test_can_create_artikel(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');
        $konflikId = $this->seedKonflik();

        Livewire::test(\App\Livewire\TambahArtikel::class, ['id' => $konflikId])
            ->set('judul_id', 'Konflik Lahan di Jawa Timur')
            ->set('judul_en', 'Land Conflict in East Java')
            ->set('deskripsi_id', 'Deskripsi konflik lahan')
            ->set('deskripsi_en', 'Description of land conflict')
            ->set('gambar', $this->makeImage())
            ->set('sumber', 'Kompas')
            ->set('tanggal_publish', '2026-06-01')
            ->call('storeDatabase')
            ->assertRedirect('/cms/konflik');

        $this->assertDatabaseHas('artikel', [
            'konflik_id' => $konflikId,
            'judul_id' => 'Konflik Lahan di Jawa Timur',
            'judul_en' => 'Land Conflict in East Java',
            'slug' => 'konflik-lahan-di-jawa-timur',
            'sumber' => 'Kompas',
        ]);
    }

    public function test_create_artikel_validation_judul_id_required(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');
        $konflikId = $this->seedKonflik();

        Livewire::test(\App\Livewire\TambahArtikel::class, ['id' => $konflikId])
            ->set('judul_id', '')
            ->set('judul_en', 'English Title')
            ->set('deskripsi_id', 'Deskripsi')
            ->set('deskripsi_en', 'Description')
            ->set('gambar', $this->makeImage())
            ->set('sumber', 'Sumber')
            ->set('tanggal_publish', '2026-06-01')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_create_artikel_validation_sumber_required(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');
        $konflikId = $this->seedKonflik();

        Livewire::test(\App\Livewire\TambahArtikel::class, ['id' => $konflikId])
            ->set('judul_id', 'Judul')
            ->set('judul_en', 'Title')
            ->set('deskripsi_id', 'Deskripsi')
            ->set('deskripsi_en', 'Description')
            ->set('gambar', $this->makeImage())
            ->set('sumber', '')
            ->set('tanggal_publish', '2026-06-01')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_create_artikel_validation_tanggal_publish_required(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');
        $konflikId = $this->seedKonflik();

        Livewire::test(\App\Livewire\TambahArtikel::class, ['id' => $konflikId])
            ->set('judul_id', 'Judul')
            ->set('judul_en', 'Title')
            ->set('deskripsi_id', 'Deskripsi')
            ->set('deskripsi_en', 'Description')
            ->set('gambar', $this->makeImage())
            ->set('sumber', 'Sumber')
            ->set('tanggal_publish', '')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_artikel_generates_slug_from_judul_id(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');
        $konflikId = $this->seedKonflik();

        Livewire::test(\App\Livewire\TambahArtikel::class, ['id' => $konflikId])
            ->set('judul_id', 'Konflik di Kalimantan & Jawa')
            ->set('judul_en', 'Conflict in Kalimantan & Java')
            ->set('deskripsi_id', 'Deskripsi konflik')
            ->set('deskripsi_en', 'Conflict description')
            ->set('gambar', $this->makeImage())
            ->set('sumber', 'CNN')
            ->set('tanggal_publish', '2026-06-15')
            ->call('storeDatabase');

        $this->assertDatabaseHas('artikel', [
            'slug' => 'konflik-di-kalimantan-jawa',
        ]);
    }

    public function test_create_artikel_duplicate_judul_id(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');
        $konflikId = $this->seedKonflik();

        DB::table('artikel')->insert([
            'konflik_id' => $konflikId,
            'judul_id' => 'Judul Duplikat',
            'judul_en' => 'Duplicate Title',
            'slug' => 'judul-duplikat',
            'deskripsi_id' => 'Original',
            'deskripsi_en' => 'Original',
        ]);

        Livewire::test(\App\Livewire\TambahArtikel::class, ['id' => $konflikId])
            ->set('judul_id', 'Judul Duplikat')
            ->set('judul_en', 'Title')
            ->set('deskripsi_id', 'Deskripsi')
            ->set('deskripsi_en', 'Description')
            ->set('gambar', $this->makeImage())
            ->set('sumber', 'Sumber')
            ->set('tanggal_publish', '2026-06-01')
            ->call('storeDatabase')
            ->assertNoRedirect();
    }

    public function test_can_view_edit_artikel_page(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');
        $konflikId = $this->seedKonflik();

        $artikelId = DB::table('artikel')->insertGetId([
            'konflik_id' => $konflikId,
            'judul_id' => 'Judul Edit',
            'judul_en' => 'Edit Title',
            'slug' => 'judul-edit',
            'deskripsi_id' => 'Deskripsi',
            'deskripsi_en' => 'Description',
            'gambar' => null,
            'sumber' => 'Sumber',
            'tanggal_publish' => '2026-06-01',
        ]);

        $this->get("/cms/edit-artikel/{$artikelId}")
            ->assertStatus(200);
    }

    public function test_can_delete_artikel(): void
    {
        $this->loginAsAdmin();
        $this->seedGroup('Test Group');
        $this->seedPerusahaan('Test Group', 'Test Perusahaan');
        $konflikId = $this->seedKonflik();

        $artikelId = DB::table('artikel')->insertGetId([
            'konflik_id' => $konflikId,
            'judul_id' => 'To Delete',
            'judul_en' => 'To Delete',
            'slug' => 'to-delete',
            'deskripsi_id' => 'Deskripsi',
            'deskripsi_en' => 'Description',
        ]);

        Livewire::test(\App\Livewire\EditArtikel::class, ['id' => $artikelId])
            ->call('deleteArtikel')
            ->assertRedirect('/cms/konflik');

        $this->assertDatabaseMissing('artikel', ['id' => $artikelId]);
    }
}
