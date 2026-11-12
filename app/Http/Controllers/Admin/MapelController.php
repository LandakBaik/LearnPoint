<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    /**
     * Tampilkan daftar mata pelajaran.
     */
    public function index(Request $request)
    {
        $query = Mapel::query()->withCount([
            'guruMapels' => function ($q) {
                $q->has('pengampuKelases');
            }
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama_mapel', 'like', "%{$search}%");
        }

        $mapels = $query->latest()->paginate(10)->withQueryString();

        $totalMapel = Mapel::count();
        $avgKkm = round(Mapel::avg('kkm') ?? 75, 1);
        $totalAlokasi = \App\Models\PengampuKelas::count();

        return view('admin.kelola_mapel.index', compact('mapels', 'totalMapel', 'avgKkm', 'totalAlokasi'));
    }

    /**
     * Form tambah mapel baru.
     */
    public function create()
    {
        return view('admin.kelola_mapel.create');
    }

    /**
     * Simpan mapel baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:100|unique:mapels,nama_mapel',
            'kkm'        => 'required|integer|min:0|max:100',
        ]);

        Mapel::create($validated);

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan!');
    }

    /**
     * Detail mata pelajaran dan daftar seluruh guru pengampunya.
     */
    public function show(Mapel $mapel)
    {
        $mapel->load(['pengampuKelases.guruMapel.guru', 'pengampuKelases.kelas']);

        $gurus = Guru::orderBy('nama', 'asc')->get();
        $kelases = Kelas::orderBy('tingkatan', 'asc')->orderBy('nama_kelas', 'asc')->get();

        return view('admin.kelola_mapel.show', compact('mapel', 'gurus', 'kelases'));
    }

    /**
     * Form edit mapel dan kelola guru pengampunya.
     */
    public function edit(Mapel $mapel)
    {
        $mapel->load(['pengampuKelases.guruMapel.guru', 'pengampuKelases.kelas']);
        $gurus = Guru::orderBy('nama', 'asc')->get();
        $kelases = Kelas::orderBy('tingkatan', 'asc')->orderBy('nama_kelas', 'asc')->get();

        return view('admin.kelola_mapel.edit', compact('mapel', 'gurus', 'kelases'));
    }

    /**
     * Update data mapel beserta penambahan guru pengampu.
     */
    public function update(Request $request, Mapel $mapel)
    {
        $validated = $request->validate([
            'nama_mapel'      => ['required', 'string', 'max:100', Rule::unique('mapels')->ignore($mapel->id)],
            'kkm'             => 'required|integer|min:0|max:100',
            'new_guru_id'     => 'nullable|exists:gurus,id',
            'new_kelas_ids'   => 'nullable|array',
            'new_kelas_ids.*' => 'exists:kelases,id',
            'new_kelas_id'    => 'nullable|exists:kelases,id',
        ]);

        $mapel->update([
            'nama_mapel' => $validated['nama_mapel'],
            'kkm'        => $validated['kkm'],
        ]);

        $newKelasIds = $request->input('new_kelas_ids', []);
        if ($request->filled('new_kelas_id') && !in_array($request->new_kelas_id, $newKelasIds)) {
            $newKelasIds[] = $request->new_kelas_id;
        }

        // Cek konflik guru lain pada kelas yang dipilih untuk mapel ini
        if (!empty($validated['new_guru_id']) && !empty($newKelasIds)) {
            foreach ($newKelasIds as $kId) {
                $existing = \App\Models\PengampuKelas::where('kelas_id', $kId)
                    ->whereHas('guruMapel', fn($q) => $q->where('mapel_id', $mapel->id))
                    ->with(['guruMapel.guru', 'kelas'])
                    ->first();

                if ($existing && $existing->guruMapel?->guru_id != $validated['new_guru_id']) {
                    $guruNama = $existing->guruMapel?->guru?->nama ?? 'Guru Lain';
                    $kelasNama = $existing->kelas?->nama_kelas ?? 'Kelas';
                    return back()->withInput()->withErrors([
                        'new_kelas_ids' => "Kelas {$kelasNama} sudah diampu oleh {$guruNama} untuk mata pelajaran {$mapel->nama_mapel}. Hanya diperbolehkan 1 guru untuk setiap mata pelajaran dalam 1 kelas."
                    ]);
                }
            }

            $gm = \App\Models\GuruMapel::firstOrCreate([
                'guru_id'  => $validated['new_guru_id'],
                'mapel_id' => $mapel->id,
            ]);
            foreach ($newKelasIds as $kId) {
                \App\Models\PengampuKelas::firstOrCreate([
                    'guru_mapel_id' => $gm->id,
                    'kelas_id'      => $kId,
                ]);
            }
        }

        return redirect()->route('mapel.edit', $mapel->id)->with('success', 'Mata pelajaran dan guru pengampu berhasil diperbarui!');
    }

    /**
     * Hapus data mapel.
     */
    public function destroy(Mapel $mapel)
    {
        $hasActivities = $mapel->pengampuKelases()
            ->where(function ($q) {
                $q->has('materis')
                    ->orHas('tugases')
                    ->orHas('quizzes')
                    ->orHas('ujians');
            })
            ->exists();

        if ($hasActivities) {
            return redirect()->route('mapel.index')->with(
                'error',
                "Mata pelajaran {$mapel->nama_mapel} tidak dapat dihapus karena sudah memiliki aktivitas pembelajaran (Materi/Tugas/Kuis/Ujian) dan nilai siswa yang aktif."
            );
        }

        $mapel->delete();

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil dihapus!');
    }
}

