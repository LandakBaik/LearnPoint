<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\PengampuKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MateriController extends Controller
{
    /**
     * GURU: Menampilkan daftar materi (dikelompokkan per grup).
     */
    public function guruIndex(Request $request)
    {
        $user = $request->user();
        $guruId = $user?->guru?->id;

        if (!$guruId) {
            return response()->json(['success' => false, 'message' => 'Profil guru tidak ditemukan.'], 404);
        }

        $query = Materi::query()
            ->whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            });

        // Search
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        // Filter Mapel
        if ($request->filled('mapel_id')) {
            $query->whereHas('pengampuKelas.guruMapel', function ($q) use ($request) {
                $q->where('mapel_id', $request->mapel_id);
            });
        }

        // Filter Tingkatan
        if ($request->filled('tingkatan')) {
            $query->whereHas('pengampuKelas.kelas', function ($q) use ($request) {
                $q->where('tingkatan', $request->tingkatan);
            });
        }

        // Filter Kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('pengampuKelas', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        $matchingGroupIds = (clone $query)->distinct()->pluck('id_grub_materi');

        $materis = Materi::whereIn('id_grub_materi', $matchingGroupIds)
            ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
            ->latest()
            ->get();

        $grouped = $materis->groupBy('id_grub_materi')->map(function ($items, $groupId) {
            $first = $items->first();
            $kelases = $items->map(function ($item) {
                return [
                    'materi_id' => $item->id,
                    'pengampu_kelas_id' => $item->pengampu_kelas_id,
                    'kelas_id' => $item->pengampuKelas?->kelas?->id,
                    'nama_kelas' => $item->pengampuKelas?->kelas?->nama_kelas,
                    'tingkatan' => $item->pengampuKelas?->kelas?->tingkatan,
                ];
            });

            return [
                'id_grub_materi' => $groupId,
                'judul' => $first->judul,
                'deskripsi' => $first->deskripsi,
                'url_youtube' => $first->url_youtube,
                'file_materi' => $first->file_materi,
                'file_url' => $first->file_materi ? asset('storage/' . $first->file_materi) : null,
                'mapel' => [
                    'id' => $first->pengampuKelas?->guruMapel?->mapel?->id,
                    'nama_mapel' => $first->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                ],
                'kelas_terkait' => $kelases,
                'created_at' => $first->created_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    /**
     * GURU: Opsi dropdown mata pelajaran dan kelas yang diampu untuk formulir tambah/edit materi.
     */
    public function guruOptions(Request $request)
    {
        $guruId = $request->user()?->guru?->id;

        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->get();

        $mapels = $pengampuKelases->pluck('guruMapel.mapel')->unique('id')->values();

        $kelases = $pengampuKelases->map(function ($pk) {
            return [
                'pengampu_kelas_id' => $pk->id,
                'mapel_id' => $pk->guruMapel?->mapel_id,
                'kelas_id' => $pk->kelas?->id,
                'nama_kelas' => $pk->kelas?->nama_kelas,
                'tingkatan' => $pk->kelas?->tingkatan,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'mapels' => $mapels,
                'kelases' => $kelases,
            ],
        ]);
    }

    /**
     * GURU: Detail materi berdasarkan grup.
     */
    public function guruShow(Request $request, string $id_grub_materi)
    {
        $guruId = $request->user()?->guru?->id;

        $materis = Materi::where('id_grub_materi', $id_grub_materi)
            ->with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
            ->get();

        if ($materis->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Materi tidak ditemukan.'], 404);
        }

        $first = $materis->first();
        if ($first->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $kelases = $materis->map(function ($item) {
            return [
                'materi_id' => $item->id,
                'pengampu_kelas_id' => $item->pengampu_kelas_id,
                'kelas_id' => $item->pengampuKelas?->kelas?->id,
                'nama_kelas' => $item->pengampuKelas?->kelas?->nama_kelas,
                'tingkatan' => $item->pengampuKelas?->kelas?->tingkatan,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id_grub_materi' => $first->id_grub_materi,
                'judul' => $first->judul,
                'deskripsi' => $first->deskripsi,
                'url_youtube' => $first->url_youtube,
                'file_materi' => $first->file_materi,
                'file_url' => $first->file_materi ? asset('storage/' . $first->file_materi) : null,
                'mapel' => [
                    'id' => $first->pengampuKelas?->guruMapel?->mapel?->id,
                    'nama_mapel' => $first->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                ],
                'kelas_terkait' => $kelases,
                'created_at' => $first->created_at,
                'updated_at' => $first->updated_at,
            ],
        ]);
    }

    /**
     * GURU: Tambah materi baru.
     */
    public function guruStore(Request $request)
    {
        $guruId = $request->user()?->guru?->id;

        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:255'],
            'mapel_id' => ['required', 'integer', 'exists:mapels,id'],
            'tingkatan' => ['required', 'in:7,8,9'],
            'pengampu_kelas_ids' => ['required', 'array', 'min:1'],
            'pengampu_kelas_ids.*' => ['required', 'integer', 'exists:pengampu_kelas,id'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'file_materi' => ['required', 'file', 'mimes:pdf,ppt,pptx', 'max:10240'],
            'url_youtube' => ['nullable', 'url', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $validPengampu = PengampuKelas::whereIn('id', $validated['pengampu_kelas_ids'])
            ->whereHas('guruMapel', function ($q) use ($guruId, $validated) {
                $q->where('guru_id', $guruId)
                  ->where('mapel_id', $validated['mapel_id']);
            })
            ->whereHas('kelas', function ($q) use ($validated) {
                $q->where('tingkatan', $validated['tingkatan']);
            })
            ->pluck('id')
            ->toArray();

        if (count($validPengampu) !== count($validated['pengampu_kelas_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat kelas sasaran yang tidak valid atau bukan wewenang Anda.',
            ], 422);
        }

        $path = $request->file('file_materi')->store('materi_files', 'public');
        $idGrubMateri = (string) Str::uuid();

        DB::transaction(function () use ($validated, $validPengampu, $path, $idGrubMateri) {
            foreach ($validPengampu as $pkId) {
                Materi::create([
                    'judul' => $validated['judul'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
                    'file_materi' => $path,
                    'url_youtube' => $validated['url_youtube'] ?? null,
                    'pengampu_kelas_id' => $pkId,
                    'id_grub_materi' => $idGrubMateri,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil ditambahkan ke ' . count($validPengampu) . ' kelas.',
            'data' => [
                'id_grub_materi' => $idGrubMateri,
                'file_url' => asset('storage/' . $path),
            ],
        ], 201);
    }

    /**
     * GURU: Perbarui grup materi.
     */
    public function guruUpdate(Request $request, string $id_grub_materi)
    {
        $guruId = $request->user()?->guru?->id;

        $materis = Materi::where('id_grub_materi', $id_grub_materi)
            ->with('pengampuKelas.guruMapel')
            ->get();

        if ($materis->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Materi tidak ditemukan.'], 404);
        }

        $firstMateri = $materis->first();
        if ($firstMateri->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'judul' => ['required', 'string', 'max:255'],
            'mapel_id' => ['required', 'integer', 'exists:mapels,id'],
            'tingkatan' => ['required', 'in:7,8,9'],
            'pengampu_kelas_ids' => ['required', 'array', 'min:1'],
            'pengampu_kelas_ids.*' => ['required', 'integer', 'exists:pengampu_kelas,id'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'file_materi' => ['nullable', 'file', 'mimes:pdf,ppt,pptx', 'max:10240'],
            'url_youtube' => ['nullable', 'url', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $validPengampu = PengampuKelas::whereIn('id', $validated['pengampu_kelas_ids'])
            ->whereHas('guruMapel', function ($q) use ($guruId, $validated) {
                $q->where('guru_id', $guruId)
                  ->where('mapel_id', $validated['mapel_id']);
            })
            ->whereHas('kelas', function ($q) use ($validated) {
                $q->where('tingkatan', $validated['tingkatan']);
            })
            ->pluck('id')
            ->toArray();

        if (count($validPengampu) !== count($validated['pengampu_kelas_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat kelas sasaran yang tidak valid atau bukan wewenang Anda.',
            ], 422);
        }

        $filePath = $firstMateri->file_materi;
        if ($request->hasFile('file_materi')) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file_materi')->store('materi_files', 'public');
        }

        DB::transaction(function () use ($id_grub_materi, $validated, $validPengampu, $filePath) {
            Materi::where('id_grub_materi', $id_grub_materi)->delete();

            foreach ($validPengampu as $pkId) {
                Materi::create([
                    'judul' => $validated['judul'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
                    'file_materi' => $filePath,
                    'url_youtube' => $validated['url_youtube'] ?? null,
                    'pengampu_kelas_id' => $pkId,
                    'id_grub_materi' => $id_grub_materi,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Grup materi berhasil diperbarui.',
            'data' => [
                'id_grub_materi' => $id_grub_materi,
                'file_url' => $filePath ? asset('storage/' . $filePath) : null,
            ],
        ]);
    }

    /**
     * GURU: Hapus grup materi.
     */
    public function guruDestroy(Request $request, string $id_grub_materi)
    {
        $guruId = $request->user()?->guru?->id;

        $materis = Materi::where('id_grub_materi', $id_grub_materi)
            ->with('pengampuKelas.guruMapel')
            ->get();

        if ($materis->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Materi tidak ditemukan.'], 404);
        }

        $firstMateri = $materis->first();
        if ($firstMateri->pengampuKelas?->guruMapel?->guru_id !== $guruId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if ($firstMateri->file_materi && Storage::disk('public')->exists($firstMateri->file_materi)) {
            Storage::disk('public')->delete($firstMateri->file_materi);
        }

        Materi::where('id_grub_materi', $id_grub_materi)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil dihapus.',
        ]);
    }

    /**
     * SISWA: Menampilkan daftar mapel materi untuk kelas siswa yang sedang login.
     */
    public function siswaIndex(Request $request)
    {
        $kelasId = $request->user()?->siswa?->kelas?->id;

        if (!$kelasId) {
            return response()->json([
                'success' => false,
                'message' => 'Data kelas siswa tidak ditemukan.',
            ], 404);
        }

        $daftarMapel = Mapel::whereHas('pengampuKelases', function ($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })
        ->with(['pengampuKelases' => function ($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId)->with(['guruMapel.guru', 'materis']);
        }])
        ->get()
        ->map(function ($mapel) {
            $materisCount = $mapel->pengampuKelases->sum(function ($pk) {
                return $pk->materis->count();
            });

            $guruPengampu = $mapel->pengampuKelases->map(function ($pk) {
                return $pk->guruMapel?->guru?->nama;
            })->filter()->unique()->values();

            return [
                'id' => $mapel->id,
                'nama_mapel' => $mapel->nama_mapel,
                'kode_mapel' => $mapel->kode_mapel,
                'total_materi' => $materisCount,
                'guru' => $guruPengampu,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $daftarMapel,
        ]);
    }

    /**
     * SISWA: Menampilkan daftar materi di dalam suatu mapel untuk siswa.
     */
    public function siswaShow(Request $request, $mapelId)
    {
        $kelasId = $request->user()?->siswa?->kelas?->id;

        if (!$kelasId) {
            return response()->json(['success' => false, 'message' => 'Data kelas siswa tidak ditemukan.'], 404);
        }

        $mapel = Mapel::whereHas('pengampuKelases', function ($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })->find($mapelId);

        if (!$mapel) {
            return response()->json(['success' => false, 'message' => 'Mata pelajaran tidak ditemukan atau bukan bagian dari kelas Anda.'], 404);
        }

        $materis = Materi::with(['pengampuKelas.guruMapel.guru'])
            ->whereHas('pengampuKelas', function ($q) use ($kelasId, $mapelId) {
                $q->where('kelas_id', $kelasId)
                  ->whereHas('guruMapel', function ($gq) use ($mapelId) {
                      $gq->where('mapel_id', $mapelId);
                  });
            })
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'id_grub_materi' => $item->id_grub_materi,
                    'judul' => $item->judul,
                    'deskripsi' => $item->deskripsi,
                    'url_youtube' => $item->url_youtube,
                    'file_materi' => $item->file_materi,
                    'file_url' => $item->file_materi ? asset('storage/' . $item->file_materi) : null,
                    'guru' => $item->pengampuKelas?->guruMapel?->guru?->nama,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'mapel' => [
                    'id' => $mapel->id,
                    'nama_mapel' => $mapel->nama_mapel,
                ],
                'materis' => $materis,
            ],
        ]);
    }
}
