<?php

namespace App\Livewire\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Lampiran PBPH: antre di memori dulu, baru ditulis ke disk saat form disimpan —
 * supaya form yang batal/gagal validasi tidak meninggalkan file yatim.
 * Dipakai TambahPbph dan EditPbph; keduanya butuh `use WithFileUploads`.
 */
trait HandlesPbphLampiran
{
    public $uploads = [];    // isi picker <input type="file" multiple>
    public $lampirans = [];  // [['id' => ?int, 'nama' => string, 'file' => string|UploadedFile]]

    /** Picker hanya melaporkan pilihan terakhir, jadi hasilnya ditumpuk ke antrean. */
    public function updatedUploads()
    {
        $this->validate([
            'uploads.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        foreach ($this->uploads as $file) {
            $this->lampirans[] = [
                'id' => null,
                'nama' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file' => $file,
            ];
        }

        $this->uploads = [];
    }

    public function removeLampiran($index)
    {
        unset($this->lampirans[$index]);
        $this->lampirans = array_values($this->lampirans);
    }

    protected function muatLampiran($pbphInfoId)
    {
        $this->lampirans = DB::table('pbph_lampiran')
            ->where('pbph_info_id', $pbphInfoId)
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => ['id' => $row->id, 'nama' => $row->nama, 'file' => $row->file])
            ->all();
    }

    /**
     * Samakan isi tabel dengan antrean form: yang dibuang dihapus (baris + file),
     * yang lama hanya diperbarui namanya, yang baru disimpan ke disk.
     */
    protected function simpanLampiran($pbphInfoId)
    {
        $idTersisa = collect($this->lampirans)->pluck('id')->filter()->all();

        $dibuang = DB::table('pbph_lampiran')
            ->where('pbph_info_id', $pbphInfoId)
            ->when($idTersisa, fn ($q) => $q->whereNotIn('id', $idTersisa))
            ->get();

        foreach ($dibuang as $row) {
            Storage::disk('public')->delete('pbph-lampiran/'.$row->file);
        }
        DB::table('pbph_lampiran')->whereIn('id', $dibuang->pluck('id'))->delete();

        foreach ($this->lampirans as $lampiran) {
            $nama = trim($lampiran['nama']) ?: 'Lampiran';

            if ($lampiran['id']) {
                DB::table('pbph_lampiran')->where('id', $lampiran['id'])->update([
                    'nama' => $nama,
                    'updated_at' => Carbon::now('Asia/Jakarta'),
                ]);
                continue;
            }

            $ext = strtolower($lampiran['file']->getClientOriginalExtension());
            $filename = uniqid().'.'.$ext;
            $lampiran['file']->storeAs('pbph-lampiran', $filename, 'public');

            DB::table('pbph_lampiran')->insert([
                'pbph_info_id' => $pbphInfoId,
                'nama' => $nama,
                'file' => $filename,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }
    }
}
