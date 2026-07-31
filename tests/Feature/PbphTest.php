<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PbphTest extends TestCase
{
    /** Daftar konsesi selalu dari GeoServer palsu — tes tidak boleh menyentuh jaringan. */
    private function fakeGeoserver(?array $features = null): void
    {
        $features ??= [
            ['properties' => ['kode_pbph' => '150006A200740', 'namobj' => 'PT PUTRADUTA INDAH WOOD']],
            ['properties' => ['kode_pbph' => '150002A201084', 'namobj' => 'PT PESONA BELANTARA PERSADA']],
            // konsesi yang sama terpecah jadi dua poligon — harus muncul sekali saja
            ['properties' => ['kode_pbph' => '150002A201084', 'namobj' => 'PT PESONA BELANTARA PERSADA']],
        ];

        Http::fake([
            '*/wfs*' => Http::response(['features' => $features], 200),
        ]);
    }

    private function seedPbphInfo(string $kode = '150006A200740', string $nama = 'PT PUTRADUTA INDAH WOOD'): int
    {
        return DB::table('pbph_info')->insertGetId([
            'kode_pbph' => $kode,
            'nama_perusahaan' => $nama,
            'izin_pertama' => 'SK.1/1998',
            'izin_saat_ini' => 'SK.90/2022',
            'luas' => 34730,
            'komisaris' => 'Budi',
            'direktur_utama' => 'Siti',
            'direktur' => 'Andi',
        ]);
    }

    /** Semua field wajib — dipakai tes yang tidak sedang menguji validasi. */
    private function isiLengkap(\Livewire\Features\SupportTesting\Testable $component): \Livewire\Features\SupportTesting\Testable
    {
        return $component
            ->set('izin_pertama', 'SK.1/1998')
            ->set('izin_saat_ini', 'SK.91/MENLHK/SETJEN/HPL.0/1/2022')
            ->set('luas', '21315.50')
            ->set('komisaris', 'Budi')
            ->set('direktur_utama', 'Siti')
            ->set('direktur', 'Andi');
    }

    public function test_can_view_pbph_pages(): void
    {
        $this->loginAsAdmin();
        $this->fakeGeoserver();

        $this->get('/cms/pbph')
            ->assertStatus(200)
            ->assertSee('PBPH');

        $this->get('/cms/tambah-pbph')->assertStatus(200);

        $id = $this->seedPbphInfo();
        DB::table('pbph_lampiran')->insert(['pbph_info_id' => $id, 'nama' => 'SK Izin', 'file' => 'abc123.pdf']);

        $this->get('/cms/editpbph/'.$id)
            ->assertStatus(200)
            ->assertSee('150006A200740')
            // baris lampiran tersimpan ikut ter-render, lengkap dengan tautan unduh
            ->assertSee('SK Izin')
            ->assertSee('/storage/pbph-lampiran/abc123.pdf');
    }

    public function test_non_admin_cannot_open_pbph_pages(): void
    {
        $this->loginAsUser();

        $this->get('/cms/pbph')->assertRedirect('/cms/dashboard');
        $this->get('/cms/tambah-pbph')->assertRedirect('/cms/dashboard');
    }

    public function test_konsesi_dropdown_is_deduplicated_and_excludes_annotated(): void
    {
        $this->loginAsAdmin();
        $this->fakeGeoserver();
        $this->seedPbphInfo('150006A200740', 'PT PUTRADUTA INDAH WOOD');

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->assertViewHas('konsesis', fn ($konsesis) => $konsesis->count() === 1
                && $konsesis->first()['kode_pbph'] === '150002A201084');
    }

    public function test_konsesi_search_filters_by_name_and_code(): void
    {
        $this->loginAsAdmin();
        $this->fakeGeoserver();

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->set('cariKonsesi', 'putraduta')
            ->assertViewHas('konsesis', fn ($k) => $k->count() === 1
                && $k->first()['kode_pbph'] === '150006A200740')
            ->set('cariKonsesi', '150002')
            ->assertViewHas('konsesis', fn ($k) => $k->count() === 1
                && $k->first()['namobj'] === 'PT PESONA BELANTARA PERSADA')
            ->set('cariKonsesi', 'tidak ada')
            ->assertViewHas('konsesis', fn ($k) => $k->isEmpty());
    }

    public function test_selecting_konsesi_sets_kode_and_label(): void
    {
        $this->loginAsAdmin();
        $this->fakeGeoserver();

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->set('cariKonsesi', 'pesona')
            ->call('selectKonsesi', '150002A201084')
            ->assertSet('kode_pbph', '150002A201084')
            // label diambil dari daftar konsesi, bukan dari argumen klik
            ->assertSet('namaKonsesi', 'PT PESONA BELANTARA PERSADA (150002A201084)')
            ->assertSet('cariKonsesi', '')
            // kode karangan diabaikan
            ->call('selectKonsesi', 'KODE-PALSU')
            ->assertSet('kode_pbph', '150002A201084');
    }

    public function test_create_rejects_kode_not_in_layer(): void
    {
        $this->loginAsAdmin();
        $this->fakeGeoserver();

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->set('kode_pbph', 'KODE-PALSU')
            ->call('storeDatabase')
            ->assertNoRedirect();

        $this->assertDatabaseCount('pbph_info', 0);
    }

    public function test_konflik_filter_by_perusahaan_keeps_checked_items_visible(): void
    {
        $this->loginAsAdmin();
        $this->fakeGeoserver();
        $sawit = $this->seedKonflik(['desa' => 'Desa Sawit', 'perusahaan' => 'PT Sawit']);
        $kayu = $this->seedKonflik(['desa' => 'Desa Kayu', 'perusahaan' => 'PT Kayu']);

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->assertViewHas('perusahaans', fn ($p) => $p->contains('PT Sawit') && $p->contains('PT Kayu'))
            // saring: hanya konflik PT Sawit
            ->set('filterPerusahaan', 'PT Sawit')
            ->assertViewHas('konfliks', fn ($k) => $k->pluck('id')->all() === [$sawit])
            // sudah dicentang lalu filter berganti — pilihan itu harus tetap terlihat
            ->set('konflikIds', [$kayu])
            ->assertViewHas('konfliks', fn ($k) => $k->pluck('id')->sort()->values()->all() === collect([$sawit, $kayu])->sort()->values()->all());
    }

    public function test_can_create_pbph_info_with_konflik_relation(): void
    {
        $this->loginAsAdmin();
        $this->fakeGeoserver();
        $konflikA = $this->seedKonflik(['desa' => 'Desa A']);
        $konflikB = $this->seedKonflik(['desa' => 'Desa B']);

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->set('kode_pbph', '150002A201084')
            ->set('izin_pertama', 'SK.1/1998')
            ->set('izin_saat_ini', 'SK.91/MENLHK/SETJEN/HPL.0/1/2022')
            ->set('luas', '21315.50')
            ->set('komisaris', 'Budi')
            ->set('direktur_utama', 'Siti')
            ->set('direktur', 'Andi')
            ->set('konflikIds', [$konflikA, $konflikB])
            ->call('storeDatabase')
            ->assertRedirect('/cms/pbph');

        $this->assertDatabaseHas('pbph_info', [
            'kode_pbph' => '150002A201084',
            // nama perusahaan diambil dari layer, bukan dari input pengguna
            'nama_perusahaan' => 'PT PESONA BELANTARA PERSADA',
            'izin_pertama' => 'SK.1/1998',
            'luas' => '21315.50',
            'direktur_utama' => 'Siti',
        ]);

        $id = DB::table('pbph_info')->where('kode_pbph', '150002A201084')->value('id');
        $this->assertDatabaseHas('konflik_pbph', ['pbph_info_id' => $id, 'konflik_id' => $konflikA]);
        $this->assertDatabaseHas('konflik_pbph', ['pbph_info_id' => $id, 'konflik_id' => $konflikB]);
    }

    /** Setiap field wajib diisi — dites satu per satu dengan dikosongkan bergantian. */
    public function test_every_field_is_required(): void
    {
        $this->loginAsAdmin();
        $this->fakeGeoserver();

        foreach (['izin_pertama', 'izin_saat_ini', 'luas', 'komisaris', 'direktur_utama', 'direktur'] as $field) {
            $this->isiLengkap(Livewire::test(\App\Livewire\TambahPbph::class))
                ->set('kode_pbph', '150002A201084')
                ->set($field, '   ')
                ->call('storeDatabase')
                ->assertNoRedirect();

            // catatan: argumen ke-3 assertDatabaseCount adalah nama koneksi, bukan pesan
            $this->assertSame(0, DB::table('pbph_info')->count(), "field {$field} seharusnya wajib diisi");
        }

        // lengkap → lolos
        $this->isiLengkap(Livewire::test(\App\Livewire\TambahPbph::class))
            ->set('kode_pbph', '150002A201084')
            ->call('storeDatabase')
            ->assertRedirect('/cms/pbph');

        $this->assertDatabaseCount('pbph_info', 1);
    }

    public function test_edit_also_requires_every_field(): void
    {
        $this->loginAsAdmin();
        $id = $this->seedPbphInfo();

        Livewire::test(\App\Livewire\EditPbph::class, ['idDB' => $id])
            ->set('komisaris', '')
            ->call('storeDatabase')
            ->assertNoRedirect();

        $this->assertDatabaseHas('pbph_info', ['id' => $id, 'komisaris' => 'Budi']);
    }

    public function test_create_requires_kode_pbph_and_rejects_duplicate(): void
    {
        $this->loginAsAdmin();
        $this->fakeGeoserver();

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->set('kode_pbph', '')
            ->call('storeDatabase')
            ->assertNoRedirect();

        $this->seedPbphInfo('150002A201084', 'PT PESONA BELANTARA PERSADA');

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->set('kode_pbph', '150002A201084')
            ->call('storeDatabase')
            ->assertNoRedirect();

        $this->assertSame(1, DB::table('pbph_info')->count());
    }

    public function test_create_rejects_non_numeric_luas(): void
    {
        $this->loginAsAdmin();
        $this->fakeGeoserver();

        $this->isiLengkap(Livewire::test(\App\Livewire\TambahPbph::class))
            ->set('kode_pbph', '150002A201084')
            ->set('luas', 'sepuluh ribu')
            ->call('storeDatabase')
            ->assertNoRedirect();

        $this->assertDatabaseCount('pbph_info', 0);
    }

    public function test_edit_updates_fields_and_replaces_konflik_links(): void
    {
        $this->loginAsAdmin();
        $id = $this->seedPbphInfo();
        $lama = $this->seedKonflik(['desa' => 'Desa Lama']);
        $baru = $this->seedKonflik(['desa' => 'Desa Baru']);
        DB::table('konflik_pbph')->insert(['pbph_info_id' => $id, 'konflik_id' => $lama]);

        Livewire::test(\App\Livewire\EditPbph::class, ['idDB' => $id])
            ->assertSet('konflikIds', [(string) $lama])
            ->set('izin_saat_ini', 'SK.90/2022')
            ->set('luas', '34730')
            ->set('konflikIds', [$baru])
            ->call('storeDatabase')
            ->assertRedirect('/cms/pbph');

        $this->assertDatabaseHas('pbph_info', ['id' => $id, 'izin_saat_ini' => 'SK.90/2022', 'luas' => '34730.00']);
        $this->assertDatabaseHas('konflik_pbph', ['pbph_info_id' => $id, 'konflik_id' => $baru]);
        $this->assertDatabaseMissing('konflik_pbph', ['pbph_info_id' => $id, 'konflik_id' => $lama]);
    }

    public function test_edit_cannot_change_kode_pbph(): void
    {
        $this->loginAsAdmin();
        $id = $this->seedPbphInfo();

        Livewire::test(\App\Livewire\EditPbph::class, ['idDB' => $id])
            ->set('kode_pbph', 'DIUBAH')
            ->call('storeDatabase');

        $this->assertDatabaseHas('pbph_info', ['id' => $id, 'kode_pbph' => '150006A200740']);
    }

    public function test_delete_removes_row_and_its_konflik_links(): void
    {
        $this->loginAsAdmin();
        $id = $this->seedPbphInfo();
        $konflik = $this->seedKonflik();
        DB::table('konflik_pbph')->insert(['pbph_info_id' => $id, 'konflik_id' => $konflik]);

        Livewire::test(\App\Livewire\CmsPbph::class)
            ->call('delete', $id)
            ->assertSet('deleter', true);

        Livewire::test(\App\Livewire\CmsPbph::class)
            ->call('deleting', $id);

        $this->assertDatabaseMissing('pbph_info', ['id' => $id]);
        $this->assertDatabaseMissing('konflik_pbph', ['pbph_info_id' => $id]);
    }

    /** Livewire tidak lewat middleware route — komponen harus menjaga dirinya sendiri. */
    public function test_non_admin_cannot_write_through_livewire(): void
    {
        $this->loginAsUser();
        $this->fakeGeoserver();
        $id = $this->seedPbphInfo();

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->set('kode_pbph', '150002A201084')
            ->call('storeDatabase')
            ->assertStatus(403);

        Livewire::test(\App\Livewire\EditPbph::class, ['idDB' => $id])
            ->set('izin_pertama', 'diubah orang lain')
            ->call('storeDatabase')
            ->assertStatus(403);

        Livewire::test(\App\Livewire\CmsPbph::class)
            ->call('deleting', $id)
            ->assertStatus(403);

        $this->assertDatabaseHas('pbph_info', ['id' => $id, 'izin_pertama' => 'SK.1/1998']);
        $this->assertSame(1, DB::table('pbph_info')->count());
    }

    public function test_can_search_pbph_by_name_and_code(): void
    {
        $this->loginAsAdmin();
        $this->seedPbphInfo('150006A200740', 'PT PUTRADUTA INDAH WOOD');
        $this->seedPbphInfo('150002A201084', 'PT PESONA BELANTARA PERSADA');

        Livewire::test(\App\Livewire\CmsPbph::class)
            ->set('search', 'PUTRADUTA')
            ->assertSee('150006A200740')
            ->assertDontSee('150002A201084');

        Livewire::test(\App\Livewire\CmsPbph::class)
            ->set('search', '150002')
            ->assertSee('PT PESONA BELANTARA PERSADA')
            ->assertDontSee('PT PUTRADUTA INDAH WOOD');
    }

    public function test_can_attach_multiple_files_on_create(): void
    {
        Storage::fake('public');
        $this->loginAsAdmin();
        $this->fakeGeoserver();

        $this->isiLengkap(Livewire::test(\App\Livewire\TambahPbph::class))
            ->set('kode_pbph', '150002A201084')
            ->set('uploads', [
                UploadedFile::fake()->create('SK Izin Pertama.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->image('peta.png'),
            ])
            // dua berkas mengantre, belum ada yang ditulis ke disk
            ->assertCount('lampirans', 2)
            ->call('storeDatabase')
            ->assertRedirect('/cms/pbph');

        $id = DB::table('pbph_info')->where('kode_pbph', '150002A201084')->value('id');
        $rows = DB::table('pbph_lampiran')->where('pbph_info_id', $id)->get();

        $this->assertCount(2, $rows);
        // nama tampil default = nama berkas asli tanpa ekstensi
        $this->assertEqualsCanonicalizing(['SK Izin Pertama', 'peta'], $rows->pluck('nama')->all());
        foreach ($rows as $row) {
            Storage::disk('public')->assertExists('pbph-lampiran/'.$row->file);
        }
    }

    public function test_picker_appends_instead_of_replacing(): void
    {
        Storage::fake('public');
        $this->loginAsAdmin();
        $this->fakeGeoserver();

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->set('uploads', [UploadedFile::fake()->create('satu.pdf', 10, 'application/pdf')])
            ->set('uploads', [UploadedFile::fake()->create('dua.pdf', 10, 'application/pdf')])
            ->assertCount('lampirans', 2)
            // picker dikosongkan lagi supaya pilihan berikutnya tidak dobel
            ->assertSet('uploads', []);
    }

    public function test_rejects_disallowed_file_type(): void
    {
        Storage::fake('public');
        $this->loginAsAdmin();
        $this->fakeGeoserver();

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->set('uploads', [UploadedFile::fake()->create('skrip.php', 10, 'application/x-php')])
            ->assertHasErrors('uploads.*')
            ->assertCount('lampirans', 0);
    }

    public function test_edit_renames_adds_and_removes_attachments(): void
    {
        Storage::fake('public');
        $this->loginAsAdmin();
        $id = $this->seedPbphInfo();

        Storage::disk('public')->put('pbph-lampiran/lama.pdf', 'isi');
        DB::table('pbph_lampiran')->insert([
            ['pbph_info_id' => $id, 'nama' => 'Dipertahankan', 'file' => 'tetap.pdf'],
            ['pbph_info_id' => $id, 'nama' => 'Dibuang', 'file' => 'lama.pdf'],
        ]);

        $component = Livewire::test(\App\Livewire\EditPbph::class, ['idDB' => $id])
            ->assertCount('lampirans', 2);

        // buang yang kedua, ganti nama yang pertama, tambah satu berkas baru
        $component->call('removeLampiran', 1)
            ->set('lampirans.0.nama', 'Nama Baru')
            ->set('uploads', [UploadedFile::fake()->create('tambahan.pdf', 10, 'application/pdf')])
            ->call('storeDatabase')
            ->assertRedirect('/cms/pbph');

        $rows = DB::table('pbph_lampiran')->where('pbph_info_id', $id)->get();
        $this->assertEqualsCanonicalizing(['Nama Baru', 'tambahan'], $rows->pluck('nama')->all());
        // file yang dibuang ikut terhapus dari disk
        Storage::disk('public')->assertMissing('pbph-lampiran/lama.pdf');
    }

    public function test_deleting_pbph_removes_attachments_and_files(): void
    {
        Storage::fake('public');
        $this->loginAsAdmin();
        $id = $this->seedPbphInfo();

        Storage::disk('public')->put('pbph-lampiran/berkas.pdf', 'isi');
        DB::table('pbph_lampiran')->insert(['pbph_info_id' => $id, 'nama' => 'Berkas', 'file' => 'berkas.pdf']);

        Livewire::test(\App\Livewire\CmsPbph::class)->call('deleting', $id);

        $this->assertDatabaseMissing('pbph_lampiran', ['pbph_info_id' => $id]);
        Storage::disk('public')->assertMissing('pbph-lampiran/berkas.pdf');
    }

    /** Request GetFeatureInfo seperti yang dikirim map.js */
    private function featureInfoUrl(): string
    {
        return '/wms-feature-info?'.http_build_query([
            'layers' => config('geoserver.layers.pbph'),
            'bbox' => '1,2,3,4',
            'width' => 800,
            'height' => 600,
            'x' => 400,
            'y' => 300,
        ]);
    }

    private function fakeFeatureInfo(string $kode = '150006A200740'): void
    {
        Http::fake(['*/wms*' => Http::response([
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => [
                    'kode_pbph' => $kode,
                    'namobj' => 'PT PUTRADUTA INDAH WOOD',
                    'no_sk' => 'K.90/MENLHK/SETJEN/HPL.0/1/2022',
                    'lssk' => 34730,
                    'jenis' => 'PBPH HP',
                ],
            ]],
        ], 200)]);
    }

    public function test_map_popup_is_enriched_with_pbph_info(): void
    {
        $this->fakeFeatureInfo();
        $id = $this->seedPbphInfo();
        $konflik = $this->seedKonflik(['desa' => 'Desa Tampil', 'status' => 'aktif', 'user_id' => null]);
        DB::table('konflik_pbph')->insert(['pbph_info_id' => $id, 'konflik_id' => $konflik]);

        $props = $this->get($this->featureInfoUrl())
            ->assertStatus(200)
            ->json('features.0.properties');

        $this->assertSame('SK.1/1998', $props['info']['izin_pertama']);
        $this->assertSame('Budi', $props['info']['komisaris']);
        $this->assertEquals(34730.0, $props['info']['luas']);
        // atribut asli dari layer tetap utuh
        $this->assertSame('PBPH HP', $props['jenis']);
        $this->assertSame([], $props['info']['lampiran']);
        // relasi konflik sengaja tidak ikut ke popup peta
        $this->assertArrayNotHasKey('konflik', $props);
    }

    public function test_map_popup_includes_attachments(): void
    {
        $this->fakeFeatureInfo();
        $id = $this->seedPbphInfo();
        DB::table('pbph_lampiran')->insert([
            ['pbph_info_id' => $id, 'nama' => 'SK Izin Pertama', 'file' => 'abc123.pdf'],
            ['pbph_info_id' => $id, 'nama' => 'Peta Konsesi', 'file' => 'def456.png'],
        ]);

        $lampiran = $this->get($this->featureInfoUrl())
            ->assertStatus(200)
            ->json('features.0.properties.info.lampiran');

        $this->assertSame(['SK Izin Pertama', 'Peta Konsesi'], array_column($lampiran, 'nama'));
        $this->assertSame('/storage/pbph-lampiran/abc123.pdf', $lampiran[0]['url']);
    }

    public function test_map_popup_never_exposes_konflik(): void
    {
        $this->fakeFeatureInfo();
        $id = $this->seedPbphInfo();
        $draft = $this->seedKonflik(['desa' => 'Desa Rahasia', 'status' => 'draft', 'user_id' => null]);
        DB::table('konflik_pbph')->insert(['pbph_info_id' => $id, 'konflik_id' => $draft]);

        $this->get($this->featureInfoUrl())
            ->assertStatus(200)
            ->assertDontSee('Desa Rahasia');
    }

    public function test_map_popup_unchanged_for_concession_without_info(): void
    {
        $this->fakeFeatureInfo('KODE-TANPA-INFO');

        $props = $this->get($this->featureInfoUrl())
            ->assertStatus(200)
            ->json('features.0.properties');

        $this->assertArrayNotHasKey('info', $props);
        $this->assertArrayNotHasKey('konflik', $props);
        $this->assertSame('PT PUTRADUTA INDAH WOOD', $props['namobj']);
    }

    public function test_map_popup_passes_geoserver_errors_through(): void
    {
        Http::fake(['*/wms*' => Http::response('<ServiceException>boom</ServiceException>', 500)]);

        $this->get($this->featureInfoUrl())
            ->assertStatus(500)
            ->assertSee('ServiceException', false);
    }

    public function test_geoserver_failure_does_not_break_the_form(): void
    {
        $this->loginAsAdmin();
        Http::fake(['*/wfs*' => Http::response('boom', 500)]);

        Livewire::test(\App\Livewire\TambahPbph::class)
            ->assertStatus(200)
            ->assertViewHas('konsesis', fn ($konsesis) => $konsesis->isEmpty());
    }
}
