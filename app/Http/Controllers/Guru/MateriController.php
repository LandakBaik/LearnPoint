<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
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
            'sort' => ['nullable', 'in:terbaru,terlama,judul_asc,judul_desc'],
        ]);

        $mapelId = $filters['mapel_id'] ?? null;
        $tingkatan = $filters['tingkatan'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;
        $search = trim($filters['search'] ?? '');
        $sort = $filters['sort'] ?? 'terbaru';

        $mapels = Mapel::query()
            ->whereHas('pengampuKelases.guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->orderBy('nama_mapel')
            ->get(['id', 'nama_mapel']);

        // Reset mapel_id jika tidak termasuk dalam mapel yang diampu guru
        if ($mapelId && !$mapels->contains('id', (int) $mapelId)) {
            $mapelId = null;
            $filters['mapel_id'] = null;
        }

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

        // Reset tingkatan jika tidak tersedia pada pilihan mapel saat ini
        if ($tingkatan && !$tingkatanOptions->contains($tingkatan)) {
            $tingkatan = null;
            $filters['tingkatan'] = null;
        }

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

        // Reset kelas_id jika tidak tersedia pada pilihan mapel & tingkatan saat ini
        if ($kelasId && !$kelasOptions->contains('id', (int) $kelasId)) {
            $kelasId = null;
            $filters['kelas_id'] = null;
        }

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

        $materisQuery = Materi::query()
            ->whereIn('id_grub_materi', $matchingGroupIds)
            ->whereHas('pengampuKelas.guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->with([
                'pengampuKelas.guruMapel.guru',
                'pengampuKelas.guruMapel.mapel',
                'pengampuKelas.kelas',
            ]);

        match ($sort) {
            'terlama' => $materisQuery->oldest(),
            'judul_asc' => $materisQuery->orderBy('judul', 'asc'),
            'judul_desc' => $materisQuery->orderBy('judul', 'desc'),
            default => $materisQuery->latest(),
        };

        $materis = $matchingGroupIds->isEmpty()
            ? collect()
            : $materisQuery->get();

        $materiGroups = $materis->groupBy('id_grub_materi');

        return view('guru.kelola_materi.index', compact(
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

        return view('guru.kelola_materi.create', compact('mapelData'));
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
            'judul' => 'required|string|max:200',
            'mapel_id' => 'required|integer|exists:mapels,id',
            'tingkatan' => 'required|in:7,8,9',
            'pengampu_kelas_ids' => 'required|array|min:1',
            'pengampu_kelas_ids.*' => 'required|integer|exists:pengampu_kelas,id',
            'deskripsi' => 'nullable|string|max:1000',
            'file_materi' => 'required|file|mimes:pdf,ppt,pptx|max:10240',
            'url_youtube' => 'nullable|url|max:2048',
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

        $judul = trim($validated['judul']);

        $duplicateMateris = Materi::with('pengampuKelas.kelas')
            ->whereIn('pengampu_kelas_id', $validPengampu)
            ->whereRaw('LOWER(judul) = ?', [Str::lower($judul)])
            ->get();

        if ($duplicateMateris->isNotEmpty()) {
            $namaKelasBentrok = $duplicateMateris
                ->map(fn ($m) => $m->pengampuKelas?->kelas?->nama_kelas)
                ->filter()
                ->unique()
                ->implode(', ');

            return back()
                ->withInput()
                ->withErrors([
                    'judul' => "Materi dengan judul \"{$judul}\" sudah ada pada kelas: {$namaKelasBentrok}.",
                ]);
        }

        $path = $request->file('file_materi')->store('materi_files', 'public');
        $idGrubMateri = (string) Str::uuid();

        try {
            DB::transaction(function () use ($validated, $judul, $validPengampu, $path, $idGrubMateri) {
                foreach ($validPengampu as $pkId) {
                    Materi::create([
                        'judul' => $judul,
                        'deskripsi' => $validated['deskripsi'],
                        'file_materi' => $path,
                        'url_youtube' => $validated['url_youtube'],
                        'pengampu_kelas_id' => $pkId,
                        'id_grub_materi' => $idGrubMateri,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        // Kirim notifikasi interaktif ke seluruh siswa pada kelas yang dipilih
        foreach ($validPengampu as $pkId) {
            $pk = PengampuKelas::with('guruMapel.mapel')->find($pkId);
            if ($pk) {
                $mapelId = $pk->guruMapel?->mapel_id;
                $mapelNama = $pk->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
                $targetUrl = $mapelId ? route('siswa.materi.show', $mapelId) : route('siswa.materi');
                Notifikasi::kirimKeKelas(
                    $pk->kelas_id,
                    "Materi Baru: {$judul}",
                    "Guru telah membagikan materi pembelajaran baru untuk mata pelajaran {$mapelNama}.",
                    'materi_baru',
                    $targetUrl,
                    $mapelId
                );
            }
        }

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

        return view('guru.kelola_materi.edit', compact(
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
            'judul' => 'required|string|max:200',
            'mapel_id' => 'required|integer|exists:mapels,id',
            'tingkatan' => 'required|in:7,8,9',
            'pengampu_kelas_ids' => 'nullable|array',
            'pengampu_kelas_ids.*' => 'required|integer|exists:pengampu_kelas,id',
            'deskripsi' => 'nullable|string|max:1000',
            'file_materi' => 'nullable|file|mimes:pdf,ppt,pptx|max:10240',
            'url_youtube' => 'nullable|url|max:2048',
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
            ->whereHas('guruMapel', function ($query) use ($guru_id, $request) {
                $query->where('guru_id', $guru_id)
                      ->where('mapel_id', $request->mapel_id);
            })
            ->whereHas('kelas', function ($query) use ($request) {
                $query->where('tingkatan', $request->tingkatan);
            })
            ->pluck('id')
            ->toArray();

        if (count($validPengampu) !== count($submittedPengampuIds)) {
            return back()->withInput()->withErrors(['pengampu_kelas_ids' => 'Terdapat kelas yang tidak valid atau bukan wewenang Anda.']);
        }

        $judul = trim($request->judul);

        $duplicateMateris = Materi::with('pengampuKelas.kelas')
            ->whereIn('pengampu_kelas_id', $validPengampu)
            ->where('id_grub_materi', '!=', $id_grub_materi)
            ->whereRaw('LOWER(judul) = ?', [Str::lower($judul)])
            ->get();

        if ($duplicateMateris->isNotEmpty()) {
            $namaKelasBentrok = $duplicateMateris
                ->map(fn ($m) => $m->pengampuKelas?->kelas?->nama_kelas)
                ->filter()
                ->unique()
                ->implode(', ');

            return back()
                ->withInput()
                ->withErrors([
                    'judul' => "Materi dengan judul \"{$judul}\" sudah ada pada kelas: {$namaKelasBentrok}.",
                ]);
        }

        $oldFilePath = $firstMateri->file_materi;
        $newFilePath = null;
        $filePath = $oldFilePath;

        if ($request->hasFile('file_materi')) {
            $newFilePath = $request->file('file_materi')->store('materi_files', 'public');
            $filePath = $newFilePath;
        }

        $existingKelasIds = $materis->pluck('pengampu_kelas_id')->toArray();
        $addedIds = array_diff($validPengampu, $existingKelasIds);

        try {
            DB::transaction(function () use ($request, $judul, $id_grub_materi, $existingKelasIds, $validPengampu, $addedIds, $filePath) {
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
                            'judul' => $judul,
                            'deskripsi' => $request->deskripsi,
                            'file_materi' => $filePath,
                            'url_youtube' => $request->url_youtube,
                        ]);
                }

                foreach ($addedIds as $pkId) {
                    Materi::create([
                        'judul' => $judul,
                        'deskripsi' => $request->deskripsi,
                        'file_materi' => $filePath,
                        'url_youtube' => $request->url_youtube,
                        'pengampu_kelas_id' => $pkId,
                        'id_grub_materi' => $id_grub_materi,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            if ($newFilePath && Storage::disk('public')->exists($newFilePath)) {
                Storage::disk('public')->delete($newFilePath);
            }
            throw $e;
        }

        // Hapus file lama hanya setelah transaksi database berhasil
        if ($newFilePath && $oldFilePath && $oldFilePath !== $newFilePath && Storage::disk('public')->exists($oldFilePath)) {
            Storage::disk('public')->delete($oldFilePath);
        }

        // Kirim notifikasi ke kelas-kelas yang baru ditambahkan saat edit
        foreach ($addedIds as $pkId) {
            $pk = PengampuKelas::with('guruMapel.mapel')->find($pkId);
            if ($pk) {
                $mapelId = $pk->guruMapel?->mapel_id;
                $mapelNama = $pk->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
                $targetUrl = $mapelId ? route('siswa.materi.show', $mapelId) : route('siswa.materi');
                Notifikasi::kirimKeKelas(
                    $pk->kelas_id,
                    "Materi Baru: {$judul}",
                    "Guru telah membagikan materi pembelajaran baru untuk mata pelajaran {$mapelNama}.",
                    'materi_baru',
                    $targetUrl,
                    $mapelId
                );
            }
        }

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
}

