<?php

namespace App\Http\Controllers;

use App\Models\GuruMapel;
use App\Models\PengampuKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuruMapelController extends Controller
{
    /**
     * Simpan penugasan guru pengampu mapel pada kelas tertentu.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id'     => 'required|exists:gurus,id',
            'mapel_id'    => 'required|exists:mapels,id',
            'kelas_ids'   => 'nullable|array',
            'kelas_ids.*' => 'exists:kelases,id',
            'kelas_id'    => 'nullable|exists:kelases,id',
        ]);

        $kelasIds = $request->input('kelas_ids', []);
        if ($request->filled('kelas_id') && !in_array($request->kelas_id, $kelasIds)) {
            $kelasIds[] = $request->kelas_id;
        }

        if (empty($kelasIds)) {
            return back()->withErrors(['kelas_ids' => 'Pilih minimal satu kelas untuk diampu.']);
        }

        $guruMapel = GuruMapel::firstOrCreate([
            'guru_id'  => $validated['guru_id'],
            'mapel_id' => $validated['mapel_id'],
        ]);

        $addedCount = 0;
        $alreadyAssignedCount = 0;
        $conflictClasses = [];

        foreach ($kelasIds as $kelasId) {
            // Cek apakah di kelas ini mapel tersebut sudah diampu oleh guru (guru ini atau guru lain)
            $existing = PengampuKelas::where('kelas_id', $kelasId)
                ->whereHas('guruMapel', function ($q) use ($validated) {
                    $q->where('mapel_id', $validated['mapel_id']);
                })
                ->with(['guruMapel.guru', 'kelas', 'guruMapel.mapel'])
                ->first();

            if ($existing) {
                if ($existing->guruMapel?->guru_id == $validated['guru_id']) {
                    // Guru yang sama sudah mengajar mapel ini di kelas ini
                    $alreadyAssignedCount++;
                } else {
                    // Konflik: Mapel ini di kelas ini sudah diampu oleh guru lain!
                    $conflictClasses[] = [
                        'kelas' => $existing->kelas?->nama_kelas ?? 'Kelas',
                        'guru'  => $existing->guruMapel?->guru?->nama ?? 'Guru Lain',
                        'mapel' => $existing->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran',
                    ];
                }
                continue;
            }

            PengampuKelas::create([
                'guru_mapel_id' => $guruMapel->id,
                'kelas_id'      => $kelasId,
            ]);
            $addedCount++;
        }

        // Jika hanya 1 penugasan dan ada konflik guru lain (misal dari modal kelola kelas)
        if (count($conflictClasses) > 0 && $addedCount === 0 && $alreadyAssignedCount === 0) {
            $c = $conflictClasses[0];
            return back()->with('error', "Mata pelajaran {$c['mapel']} di kelas {$c['kelas']} sudah diampu oleh {$c['guru']}. Dalam 1 kelas hanya diperbolehkan 1 guru untuk setiap mata pelajaran.");
        }

        if ($addedCount === 0 && count($conflictClasses) === 0) {
            return back()->with('warning', 'Semua kelas yang Anda pilih sudah terdaftar sebelumnya untuk guru ini. Tidak ada data baru yang ditambahkan.');
        }

        $messages = [];
        if ($addedCount > 0) {
            $messages[] = "Berhasil menugaskan guru ke {$addedCount} kelas!";
        }
        if ($alreadyAssignedCount > 0) {
            $messages[] = "({$alreadyAssignedCount} kelas dilewati karena sudah terdaftar sebelumnya untuk guru ini).";
        }
        if (count($conflictClasses) > 0) {
            $conflictDetails = collect($conflictClasses)->map(fn($c) => "{$c['kelas']} (diampu {$c['guru']})")->join(', ');
            $messages[] = "Dilewati karena sudah memiliki guru pengampu lain: {$conflictDetails}.";
        }

        $flashType = $addedCount > 0 ? 'success' : 'warning';
        return back()->with($flashType, implode(' ', $messages));
    }

    /**
     * Hapus penugasan guru pengampu mapel pada kelas tertentu.
     */
    public function destroy(PengampuKelas $guruMapel)
    {
        $parentGuruMapel = $guruMapel->guruMapel;
        $guruMapel->delete();

        // Jika guru sudah tidak mengajar di kelas mana pun untuk mapel ini,
        // bersihkan data guru_mapel dan file fotonya agar tidak menggantung di kelola jadwal.
        if ($parentGuruMapel && $parentGuruMapel->pengampuKelases()->count() === 0) {
            if ($parentGuruMapel->jadwal && Storage::disk('public')->exists($parentGuruMapel->jadwal)) {
                Storage::disk('public')->delete($parentGuruMapel->jadwal);
            }
            $parentGuruMapel->delete();
        }

        return back()->with('success', 'Penugasan guru pengampu mapel berhasil dihapus!');
    }
}
