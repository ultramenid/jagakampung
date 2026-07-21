<?php

namespace App\Livewire;

use Illuminate\Support\Facades\{DB, Storage};
use Carbon\Carbon;
use Livewire\{Component, WithFileUploads};
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Masmerise\Toaster\Toaster;

class EditKonflik extends Component
{
    use WithFileUploads;
    public $deleter, $isAdd, $isEdit, $isPelaku;
    public $chooseRegion = "";
    public $region = "Pilih desa";
    public $administrasi = [],
        $lampirans = [],
        $images = [];
    public $selectedGroup, $selectedPerusahaan, $selectedStatus;
    public $lembaga = "",
        $lembagas = [],
        $chooselembaga = "";
    public $namalampiran;
    public $filelampiran;
    public $editIndex = null;
    public $luas, $kk, $jiwa, $deskripsikonflik, $deskripsiperjuangan;
    public $provinsi,
        $kabkota,
        $kecamatan,
        $desa,
        $latitude,
        $longtitude,
        $geom;
    public $idDB;

    public function mount($idDB)
    {
        // dd($idDB);
        $this->idDB = $idDB;

        $data = DB::table("konflik")->where("konflik.id", $idDB)->first();

        // dd($data);
        // ponytail: IDOR guard — only the owner or an admin may edit this konflik
        if (
            (int) session("role_id") !== 0 &&
            (int) ($data->user_id ?? 0) !== (int) session("id")
        ) {
            abort(403, "Anda tidak berhak mengedit konflik ini.");
        }
        $this->provinsi = $data->provinsi;
        $this->kabkota = $data->kabkota;
        $this->kecamatan = $data->kecamatan;
        $this->desa = $data->desa;
        $this->latitude = $data->lat;
        $this->longtitude = $data->long;
        $this->luas = (float) $data->luas;
        $this->kk = $data->kk;
        $this->jiwa = $data->jiwa;
        $this->deskripsikonflik = $data->deskripsikonflik;
        $this->deskripsiperjuangan = $data->deskripsiperjuangan;
        $this->selectedStatus = $data->status;
        $this->selectedGroup = $data->group;
        $this->selectedPerusahaan = $data->perusahaan;
        // ponytail: portable Query Builder calls instead of MySQL-only JSON_ARRAYAGG/JSON_OBJECT
        // raw SQL — production runs Postgres, which doesn't support that syntax
        $this->lembagas = DB::table("konflik_lembaga")->where("konflik_id", $idDB)->pluck("nama")->toArray();
        $this->lampirans = DB::table("konflik_lampiran")
            ->where("konflik_id", $idDB)
            ->get(["nama", "file"])
            ->map(fn ($row) => (array) $row)
            ->toArray();
        $this->images = DB::table("konflik_gambar")->where("konflik_id", $idDB)->pluck("nama")->toArray();
        $this->chooseRegion = $data->desa;

        $this->region = "{$this->provinsi} | {$this->kabkota} | {$this->kecamatan} | {$this->desa} ";

        // ponytail: the `geom` column in mv_level_6_id is already GeoJSON text (not a PostGIS geometry),
        // so we select it raw — ST_AsGeoJSON() chokes on it with "parse error - invalid geometry".
        // Match all four fields (not just desa) so we re-fetch the same admin row the user
        // originally selected — desa names alone can collide across different kecamatan/kabkota/provinsi.
        $geom = DB::connection("pgsql_gis")
            ->table("proteus.mv_level_6_id")
            ->select("geom", "name", "latitude", "longtitude")
            ->where("name", "ILIKE", "%" . $this->desa . "%")
            ->where("name", "ILIKE", "%" . $this->kecamatan . "%")
            ->where("name", "ILIKE", "%" . $this->kabkota . "%")
            ->where("name", "ILIKE", "%" . $this->provinsi . "%")
            ->first();

        $this->geom = $geom?->geom;
        $this->dispatch("connected", [
            "latitude" => $this->latitude,
            "longitude" => $this->longtitude,
            "geom" => $this->geom,
        ]);
    }

    public function manualValidation()
    {
        // dd('masuk');
        if ($this->desa == "") {
            // dd('masuk');
            Toaster::error("Silahkan pilih wilayah konflik!");
            return false;
        } elseif ($this->latitude == "" || $this->longtitude == "") {
            Toaster::error("Silahkan pilih titik koordinat konflik pada peta!");
            return false;
        } elseif ($this->luas == "") {
            Toaster::error("Silahkan isi luas konflik!");
            return false;
        } elseif ($this->selectedStatus == "") {
            Toaster::error("Silahkan pilih status konflik!");
            return false;
        } elseif ($this->deskripsikonflik == "") {
            Toaster::error("Silahkan isi deskripsi konflik!");
            return false;
        } elseif ($this->deskripsiperjuangan == "") {
            Toaster::error("Silahkan isi deskripsi perjuangan!");
            return false;
        } elseif ($this->selectedGroup == "") {
            Toaster::error("Silahkan pilih group perusahaan!");
            return false;
        } elseif ($this->selectedPerusahaan == "") {
            Toaster::error("Silahkan pilih perusahaan!");
            return false;
        }

        return true;
    }

    public function storeDatabase()
    {
        // ponytail: non-admins may never publish (mirrors TambahKonflik)
        if ((int) session("role_id") !== 0) {
            $this->selectedStatus = "draft";
        }
        // ponytail: validate new image uploads (no mimes rule existed before)
        $this->validate([
            "newImages.*" => "nullable|image|mimes:jpg,jpeg,png,webp",
            "lembagas" => "nullable|array",
            "lembagas.*" => "nullable|string",
        ]);

        // ponytail: re-check ownership on every save (mount's 403 only fires on first render; idDB is client-mutable)
        $konflik = DB::table("konflik")->where("id", $this->idDB)->first();
        if (
            !$konflik ||
            ((int) session("role_id") !== 0 &&
                (int) ($konflik->user_id ?? 0) !== (int) session("id"))
        ) {
            abort(403, "Anda tidak berhak mengedit konflik ini.");
        }

        if ($this->manualValidation()) {
            // Simpan data konflik
            $konflikId = DB::table("konflik")
                ->where("id", $this->idDB)
                ->update([
                    "provinsi" => $this->provinsi,
                    "kabkota" => $this->kabkota,
                    "kecamatan" => $this->kecamatan,
                    "desa" => $this->desa,
                    "lat" => $this->latitude,
                    "long" => $this->longtitude,
                    "luas" => $this->luas,
                    "group" => $this->selectedGroup,
                    "perusahaan" => $this->selectedPerusahaan,
                    "kk" => $this->kk === "" ? null : $this->kk,
                    "jiwa" => $this->jiwa === "" ? null : $this->jiwa,
                    "deskripsikonflik" => $this->deskripsikonflik,
                    "deskripsiperjuangan" => $this->deskripsiperjuangan,
                    "status" => $this->selectedStatus,
                    "updated_at" => Carbon::now("Asia/Jakarta"),
                ]);

            // update atau insert lembaga (opsional — boleh dikosongkan)
            foreach ($this->lembagas ?? [] as $lembaga) {
                DB::table("konflik_lembaga")->updateOrInsert(
                    ["konflik_id" => $this->idDB, "nama" => $lembaga],
                    ["updated_at" => Carbon::now("Asia/Jakarta")],
                );
            }

            // hapus lembaga yang sudah dihapus dari form (termasuk saat dikosongkan semua)
            DB::table("konflik_lembaga")
                ->where("konflik_id", $this->idDB)
                ->whereNotIn("nama", $this->lembagas ?? [])
                ->delete();

            // Save lampiran
            $newFilenames = [];

            foreach ($this->lampirans as $lampiran) {
                $isTemp = $lampiran["file"] instanceof TemporaryUploadedFile;

                if ($isTemp) {
                    $ext = strtolower($lampiran["file"]->getClientOriginalExtension());
                    $filenamelampiran = uniqid() . "." . $ext;
                    $lampiran["file"]->storeAs(
                        "lampiran",
                        $filenamelampiran,
                        "public",
                    );
                } else {
                    $filenamelampiran = $lampiran["file"];
                }

                $newFilenames[] = $filenamelampiran;

                DB::table("konflik_lampiran")->updateOrInsert(
                    ["konflik_id" => $this->idDB, "file" => $filenamelampiran],
                    [
                        "nama" => $lampiran["nama"],
                        "file" => $filenamelampiran,
                        "updated_at" => Carbon::now("Asia/Jakarta"),
                    ],
                );
            }

            // hapus file lama yang tidak ada di $newFilenames
            $oldLampirans = DB::table("konflik_lampiran")
                ->where("konflik_id", $this->idDB)
                ->pluck("file")
                ->toArray();
            $deletedLampirans = array_diff($oldLampirans, $newFilenames);

            foreach ($deletedLampirans as $deleted) {
                Storage::disk("public")->delete("lampiran/" . $deleted);
                DB::table("konflik_lampiran")
                    ->where("konflik_id", $this->idDB)
                    ->where("file", $deleted)
                    ->delete();
            }

            //    save gambar
            $allImages = array_merge($this->images, $this->newImages);

            // kumpulkan semua filename yang akan disimpan
            $newFilenames = [];

            foreach ($allImages as $image) {
                $isTemp = $image instanceof TemporaryUploadedFile;

                if ($isTemp) {
                    $ext = strtolower($image->getClientOriginalExtension());
                    $filenamegambar = uniqid() . "." . $ext;
                    $image->storeAs("gambar", $filenamegambar, "public");
                } else {
                    $filenamegambar = $image;
                }

                $newFilenames[] = $filenamegambar;
            }

            // hapus file lama yang tidak ada di $newFilenames
            $oldGambars = DB::table("konflik_gambar")
                ->where("konflik_id", $this->idDB)
                ->pluck("nama")
                ->toArray();
            $deletedGambars = array_diff($oldGambars, $newFilenames);

            foreach ($deletedGambars as $deleted) {
                Storage::disk("public")->delete("gambar/" . $deleted);
                DB::table("konflik_gambar")
                    ->where("konflik_id", $this->idDB)
                    ->where("nama", $deleted)
                    ->delete();
            }

            // update DB dengan semua filename (encode jadi JSON atau sesuai struktur tabel)

            // dd($newFilenames);
            foreach ($newFilenames as $filename) {
                DB::table("konflik_gambar")
                    ->where("konflik_id", $this->idDB)
                    ->updateOrInsert(
                        ["konflik_id" => $this->idDB, "nama" => $filename],
                        [
                            "nama" => $filename,
                            "updated_at" => Carbon::now("Asia/Jakarta"),
                        ],
                    );
            }
            redirect()->to("/cms/konflik");
        }
    }

    public function simpanLampiran()
    {
        $this->validate([
            "namalampiran" => "required|string",
            "filelampiran" => "required|mimes:pdf,jpg,jpeg,png,webp",
        ]);

        $this->lampirans[] = [
            "nama" => $this->namalampiran,
            "file" => $this->filelampiran,
            "filename" => $this->filelampiran->getClientOriginalName(),
        ];

        $this->resetForm();
        // dd($this->lampirans);
        $this->dispatch("close-form");
    }

    public $newImages = [];

    protected $previousNewImages = [];

    // File inputs only ever report the current picker selection — without this,
    // picking a 4th image would wipe out the 3 already staged instead of adding to them.
    public function updatingNewImages($value)
    {
        $this->previousNewImages = $this->newImages;
    }

    public function updatedNewImages($value)
    {
        $this->newImages = array_merge($this->previousNewImages, $this->newImages);
    }

    public function removeNewImage($index)
    {
        unset($this->newImages[$index]);
        $this->newImages = array_values($this->newImages);
    }

    // toggle form
    public function addLampiran()
    {
        if ($this->isAdd || $this->isEdit) {
            $this->resetForm();
            return;
        }

        $this->isAdd = true;
    }

    public function updateLampiranTemp()
    {
        if ($this->editIndex === null) {
            return;
        }

        $this->validate([
            "namalampiran" => "required|string",
            "filelampiran" => "nullable|mimes:pdf,jpg,jpeg,png,webp",
        ]);

        $this->lampirans[$this->editIndex]["nama"] = $this->namalampiran;

        // kalau upload file baru → ganti
        if ($this->filelampiran) {
            $this->lampirans[$this->editIndex]["file"] = $this->filelampiran;

            $this->lampirans[$this->editIndex][
                "filename"
            ] = $this->filelampiran->getClientOriginalName();
        }

        // penting: reindex
        $this->lampirans = array_values($this->lampirans);

        $this->resetForm();
        $this->dispatch("close-form");
    }

    public function setLembaga($lembaga)
    {
        if (!in_array($lembaga, $this->lembagas)) {
            array_push($this->lembagas, $lembaga);
        }
        $this->chooselembaga = $lembaga;
        $this->lembaga = "";
    }

    public function deleteTags($id)
    {
        unset($this->lembagas[$id]);
        $this->lembaga = "";
        empty($this->lembagas) ? ($this->lembaga = "Pilih lembaga") : null;
        $this->chooselembaga = "";
    }

    public function resetForm()
    {
        $this->namalampiran = null;
        $this->filelampiran = null;
        $this->isAdd = false;
        $this->isEdit = false;
        $this->editIndex = null;

        $this->resetValidation();
    }

    public function editPerkembangan($index)
    {
        // dd($this->lampirans[$index]);
        if (!isset($this->lampirans[$index])) {
            return;
        }

        $this->editIndex = $index;
        $this->isEdit = true;
        $this->isAdd = false;

        // dd($this->lampirans[$index]->nama);
        $this->namalampiran = $this->lampirans[$index]["nama"];

        // file tidak bisa diprefill, biarkan kosong
        $this->filelampiran = null;
    }

    public function selectRegion($value, $latitude, $longtitude, $geom)
    {
        // dd($latitude, $longtitude);
        $this->latitude = $latitude;
        $this->longtitude = $longtitude;
        $this->geom = $geom;

        $this->dispatch("connected", [
            "latitude" => (float) $latitude,
            "longtitude" => (float) $longtitude,
            "geom" => $geom,
        ]);
        $parts = preg_split("/[\[\]\|]+/", $value, -1, PREG_SPLIT_NO_EMPTY);

        [
            $this->desa,
            $this->kecamatan,
            $this->kabkota,
            $this->provinsi,
        ] = array_slice($parts, 0, 4) + [null, null, null, null];

        $this->region = "{$this->provinsi} | {$this->kabkota} | {$this->kecamatan} | {$this->desa} ";
        $this->chooseRegion = "";
        $this->administrasi = [];

        $this->dispatch("close-region");
    }

    public function delete($index)
    {
        unset($this->lampirans[$index]);

        $this->lampirans = array_values($this->lampirans);

        // kalau sedang edit item yang dihapus
        if ($this->editIndex === $index) {
            $this->resetForm();
        }
    }
    public function updatedChooseRegion()
    {
        if (strlen($this->chooseRegion) < 3) {
            $this->administrasi = [];
            return;
        }

        $this->administrasi = DB::connection("pgsql_gis")
            ->table("proteus.mv_level_6_id")
            ->select("geom", "name", "latitude", "longtitude")
            ->where("name", "ILIKE", "%" . $this->chooseRegion . "%")
            ->get();
    }

    public function removeImage($index)
    {
        unset($this->images[$index]);

        $this->images = array_values($this->images);
    }

    public function getPerusahaan()
    {
        return DB::table("perusahaans")
            ->where("group", $this->selectedGroup)
            ->get();
    }
    public function getLembaga()
    {
        return DB::table("instansi")->get();
    }
    public function getGroup()
    {
        return DB::table("groups")->get();
    }
    public function render()
    {
        $groups = $this->getGroup();
        $perusahaans = $this->getPerusahaan();
        $listlembaga = $this->getLembaga();
        return view(
            "livewire.edit-konflik",
            compact("groups", "perusahaans", "listlembaga"),
        );
    }
}
