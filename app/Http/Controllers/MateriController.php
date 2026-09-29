<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PengampuKelas;
use App\Models\GuruMapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MateriController extends Controller
{
    /**
     * Halaman Utama / Kelola Materi untuk Guru.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $guruId = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guruId, 403);

        $filters = $request->validate([
            'mapel_id' => ['nullable', 'integer', 'exists:mapels,id'],
            'tingkatan' => ['nullable', 'in:7,8,9'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelases,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $mapelId = $filters['mapel_id'] ?? null;
        $tingkatan = $filters['tingkatan'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;
        $search = trim($filters['search'] ?? '');

        $mapels = Mapel::query()
            ->whereHas('pengampuKelases.guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->orderBy('nama_mapel')
            ->get(['id', 'nama_mapel']);

        $tingkatanOptions = Kelas::query()
            ->whereHas('pengampuKelases.guruMapel', function ($query) use ($guruId, $mapelId) {
                $query->where('guru_id', $guruId)
                    ->when($mapelId, function ($query) use ($mapelId) {
                        $query->where('mapel_id', $mapelId);
                    });
            })
            ->distinct()
            ->orderBy('tingkatan')
            ->pluck('tingkatan');

        $kelasOptions = Kelas::query()
            ->whereHas('pengampuKelases.guruMapel', function ($query) use ($guruId, $mapelId) {
                $query->where('guru_id', $guruId)
                    ->when($mapelId, function ($query) use ($mapelId) {
                        $query->where('mapel_id', $mapelId);
                    });
            })
            ->when($tingkatan, function ($query) use ($tingkatan) {
                $query->where('tingkatan', $tingkatan);
            })
            ->orderBy('tingkatan')
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tingkatan']);

        $matchingMateri = Materi::query()
            ->whereHas('pengampuKelas', function ($assignment) use ($guruId, $mapelId, $tingkatan, $kelasId) {
                $assignment->whereHas('guruMapel', function ($guruMapel) use ($guruId, $mapelId) {
                    $guruMapel->where('guru_id', $guruId)
                        ->when($mapelId, function ($query) use ($mapelId) {
                            $query->where('mapel_id', $mapelId);
                        });
                })
                    ->when($tingkatan, function ($query) use ($tingkatan) {
                        $query->whereHas('kelas', function ($kelas) use ($tingkatan) {
                            $kelas->where('tingkatan', $tingkatan);
                        });
                    })
                    ->when($kelasId, function ($query) use ($kelasId) {
                        $query->where('kelas_id', $kelasId);
                    });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where('judul', 'like', '%' . $search . '%');
            });

        $matchingGroupIds = (clone $matchingMateri)
            ->select('id_grub_materi')
            ->distinct()
            ->pluck('id_grub_materi');

        $materis = $matchingGroupIds->isEmpty()
            ? collect()
            : Materi::query()
                ->whereIn('id_grub_materi', $matchingGroupIds)
                ->whereHas('pengampuKelas.guruMapel', function ($query) use ($guruId) {
                    $query->where('guru_id', $guruId);
                })
                ->with([
                    'pengampuKelas.guruMapel.guru',
                    'pengampuKelas.guruMapel.mapel',
                    'pengampuKelas.kelas',
                ])
                ->orderBy('judul')
                ->get();

        $materiGroups = $materis->groupBy('id_grub_materi');

        return view('Guru.Materi', compact(
            'materis',
            'materiGroups',
            'mapels',
            'tingkatanOptions',
            'kelasOptions',
            'filters'
        ));
    }

    /**
     * Halaman Tambah Materi untuk Guru.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $guruId = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guruId, 403);

        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->get();

        $mapelData = [];
        foreach ($pengampuKelases as $pk) {
            $mapel = $pk->guruMapel->mapel;
            $kelas = $pk->kelas;
            if (!$mapel || !$kelas) continue;

            if (!isset($mapelData[$mapel->id])) {
                $mapelData[$mapel->id] = [
                    'id' => $mapel->id,
                    'nama' => $mapel->nama_mapel,
                    'tingkatans' => []
                ];
            }
            if (!isset($mapelData[$mapel->id]['tingkatans'][$kelas->tingkatan])) {
                $mapelData[$mapel->id]['tingkatans'][$kelas->tingkatan] = [];
            }
            $mapelData[$mapel->id]['tingkatans'][$kelas->tingkatan][] = [
                'kelas_id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'pengampu_kelas_id' => $pk->id,
            ];
        }

        usort($mapelData, fn($a, $b) => strcmp($a['nama'], $b['nama']));
        foreach ($mapelData as &$m) {
            ksort($m['tingkatans']);
            foreach ($m['tingkatans'] as &$kelasArr) {
                usort($kelasArr, fn($a, $b) => strcmp($a['nama_kelas'], $b['nama_kelas']));
            }
        }

        return view('Guru.MateriCreate', compact('mapelData'));
    }

    /**
     * Proses simpan Materi.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $guruId = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guruId, 403);

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'mapel_id' => 'required|integer|exists:mapels,id',
            'tingkatan' => 'required|in:7,8,9',
            'pengampu_kelas_ids' => 'required|array|min:1',
            'pengampu_kelas_ids.*' => 'required|integer|exists:pengampu_kelas,id',
            'deskripsi' => 'nullable|string|max:1000',
            'file_materi' => 'required|file|mimes:pdf,ppt,pptx|max:10240',
            'url_youtube' => 'nullable|url|max:255',
        ], [
            'file_materi.required' => 'File materi wajib diunggah (PDF/PPT/PPTX).',
            'file_materi.mimes' => 'Format file harus PDF, PPT, atau PPTX.',
            'file_materi.max' => 'Ukuran file maksimal 10 MB.',
            'pengampu_kelas_ids.required' => 'Pilih minimal satu kelas sasaran.',
        ]);

        $validPengampu = PengampuKelas::whereIn('id', $validated['pengampu_kelas_ids'])
            ->whereHas('guruMapel', function ($query) use ($guruId, $validated) {
                $query->where('guru_id', $guruId)
                      ->where('mapel_id', $validated['mapel_id']);
            })
            ->whereHas('kelas', function ($query) use ($validated) {
                $query->where('tingkatan', $validated['tingkatan']);
            })
            ->pluck('id')
            ->toArray();

        if (count($validPengampu) !== count($validated['pengampu_kelas_ids'])) {
            return back()->withInput()->withErrors(['pengampu_kelas_ids' => 'Terdapat kelas sasaran yang tidak valid atau bukan wewenang Anda.']);
        }

        $path = $request->file('file_materi')->store('materi_files', 'public');
        $idGrubMateri = (string) Str::uuid();

        DB::transaction(function () use ($validated, $validPengampu, $path, $idGrubMateri) {
            foreach ($validPengampu as $pkId) {
                Materi::create([
                    'judul' => $validated['judul'],
                    'deskripsi' => $validated['deskripsi'],
                    'file_materi' => $path,
                    'url_youtube' => $validated['url_youtube'],
                    'pengampu_kelas_id' => $pkId,
                    'id_grub_materi' => $idGrubMateri,
                ]);
            }
        });

        return redirect()->route('guru.materi')->with('success', 'Materi berhasil ditambahkan ke ' . count($validPengampu) . ' kelas.');
    }
    public function edit(string $id_grub_materi, Request $request)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guru_id, 403);

        // Ambil semua row materi dalam grup ini
        $materis = Materi::with('pengampuKelas.kelas')
            ->where('id_grub_materi', $id_grub_materi)
            ->get();

        if ($materis->isEmpty()) {
            abort(404, 'Materi tidak ditemukan');
        }

        // Verifikasi kepemilikan
        $firstMateri = $materis->first();
        if ($firstMateri->pengampuKelas->guruMapel->guru_id !== $guru_id) {
            abort(403, 'Akses ditolak');
        }

        // Data mapel_id dan tingkatan awal
        $selectedMapelId = $firstMateri->pengampuKelas->guruMapel->mapel_id;
        $selectedTingkatan = $firstMateri->pengampuKelas->kelas->tingkatan;
        $selectedKelasIds = $materis->pluck('pengampu_kelas_id')->toArray();

        $guruMapels = GuruMapel::with(['mapel', 'pengampuKelases.kelas'])
            ->where('guru_id', $guru_id)
            ->get();

        $mapels = $guruMapels->pluck('mapel')->unique('id');

        $kelasPerMapel = [];
        foreach ($guruMapels as $gm) {
            $mapelId = $gm->mapel_id;
            if (!isset($kelasPerMapel[$mapelId])) {
                $kelasPerMapel[$mapelId] = [];
            }

            foreach ($gm->pengampuKelases as $pk) {
                $tingkat = $pk->kelas->tingkatan;
                if (!isset($kelasPerMapel[$mapelId][$tingkat])) {
                    $kelasPerMapel[$mapelId][$tingkat] = [];
                }
                $kelasPerMapel[$mapelId][$tingkat][] = [
                    'pengampu_kelas_id' => $pk->id,
                    'nama_kelas' => $pk->kelas->nama_kelas
                ];
            }
        }

        return view('Guru.MateriEdit', compact(
            'materis',
            'firstMateri',
            'mapels',
            'kelasPerMapel',
            'selectedMapelId',
            'selectedTingkatan',
            'selectedKelasIds',
            'id_grub_materi'
        ));
    }

    public function update(Request $request, string $id_grub_materi)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $materis = Materi::with('pengampuKelas.guruMapel')
            ->where('id_grub_materi', $id_grub_materi)
            ->get();

        if ($materis->isEmpty()) {
            abort(404, 'Materi tidak ditemukan');
        }

        $firstMateri = $materis->first();
        if ($firstMateri->pengampuKelas->guruMapel->guru_id !== $guru_id) {
            abort(403, 'Akses ditolak');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'pengampu_kelas_ids' => 'nullable|array',
            'pengampu_kelas_ids.*' => 'required|integer|exists:pengampu_kelas,id',
            'deskripsi' => 'nullable|string|max:1000',
            'file_materi' => 'nullable|file|mimes:pdf,ppt,pptx|max:10240',
            'url_youtube' => 'nullable|url|max:255',
        ]);

        $submittedPengampuIds = $request->pengampu_kelas_ids ?? [];

        // Jika semua kelas dilepas / dicentang kosong, hapus materi ini sepenuhnya
        if (empty($submittedPengampuIds)) {
            $filePath = $firstMateri->file_materi;
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            Materi::where('id_grub_materi', $id_grub_materi)->delete();

            return redirect()->route('guru.materi')->with('success', 'Materi berhasil dihapus dari seluruh kelas.');
        }

        $validPengampu = PengampuKelas::whereIn('id', $submittedPengampuIds)
            ->whereHas('guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })->pluck('id')->toArray();

        if (count($validPengampu) !== count($submittedPengampuIds)) {
            return back()->withInput()->withErrors(['pengampu_kelas_ids' => 'Terdapat kelas yang tidak valid atau bukan wewenang Anda.']);
        }

        $filePath = $firstMateri->file_materi;
        if ($request->hasFile('file_materi')) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file_materi')->store('materi_files', 'public');
        }

        DB::transaction(function () use ($request, $id_grub_materi, $materis, $validPengampu, $filePath) {
            $existingKelasIds = $materis->pluck('pengampu_kelas_id')->toArray();

            $removedIds = array_diff($existingKelasIds, $validPengampu);
            if (!empty($removedIds)) {
                Materi::where('id_grub_materi', $id_grub_materi)
                    ->whereIn('pengampu_kelas_id', $removedIds)
                    ->delete();
            }

            $keptIds = array_intersect($existingKelasIds, $validPengampu);
            if (!empty($keptIds)) {
                Materi::where('id_grub_materi', $id_grub_materi)
                    ->whereIn('pengampu_kelas_id', $keptIds)
                    ->update([
                        'judul' => $request->judul,
                        'deskripsi' => $request->deskripsi,
                        'file_materi' => $filePath,
                        'url_youtube' => $request->url_youtube,
                    ]);
            }

            $addedIds = array_diff($validPengampu, $existingKelasIds);
            foreach ($addedIds as $pkId) {
                Materi::create([
                    'judul' => $request->judul,
                    'deskripsi' => $request->deskripsi,
                    'file_materi' => $filePath,
                    'url_youtube' => $request->url_youtube,
                    'pengampu_kelas_id' => $pkId,
                    'id_grub_materi' => $id_grub_materi,
                ]);
            }
        });

        return redirect()->route('guru.materi')->with('success', 'Grup materi berhasil diperbarui.');
    }

    public function destroy(Request $request, string $id_grub_materi)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $materis = Materi::with('pengampuKelas.guruMapel')
            ->where('id_grub_materi', $id_grub_materi)
            ->get();

        if ($materis->isEmpty()) {
            abort(404, 'Materi tidak ditemukan');
        }

        // Verifikasi kepemilikan
        $firstMateri = $materis->first();
        abort_unless($firstMateri->pengampuKelas->guruMapel->guru_id === $guru_id, 403);

        // Hapus file dari storage (cukup sekali karena satu grup = satu file)
        $filePath = $firstMateri->file_materi;
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        // Hapus semua baris materi dalam grup ini
        Materi::where('id_grub_materi', $id_grub_materi)->delete();

        return redirect()->route('guru.materi')->with('success', 'Materi berhasil dihapus.');
    }

    /**
     * Halaman Detail Bab & File Materi per Mapel untuk Siswa.
     */
    public function indexSiswa(Request $request)
    {
        $kelasId = $request->user()?->siswa?->kelas?->id;

        $daftarMapel = $kelasId
            ? Mapel::whereHas('pengampuKelases', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
                ->with(['pengampuKelases' => function ($query) use ($kelasId) {
                    $query->where('kelas_id', $kelasId)
                        ->with(['guruMapel.guru', 'materis']);
                }])
                ->get()
            : collect();

        return view('siswa.materi.index', compact('daftarMapel'));
    }

    /**
     * Method milikmu untuk menampilkan detail materi dari mapel yang dipilih
     */
    public function showSiswa(Request $request, $id)
    {
        $kelasId = $request->user()?->siswa?->kelas?->id;

        abort_unless($kelasId, 404);

        $mapel = Mapel::whereHas('pengampuKelases', function ($query) use ($kelasId) {
            $query->where('kelas_id', $kelasId);
        })->findOrFail($id);

        $materis = Materi::with([
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.kelas',
        ])
            ->whereHas('pengampuKelas', function ($query) use ($kelasId, $id) {
                $query->where('kelas_id', $kelasId)
                    ->whereHas('guruMapel', function ($query) use ($id) {
                        $query->where('mapel_id', $id);
                    });
            })
            ->get();

        return view('siswa.materi.show', compact('mapel', 'materis'));
    }

}
