<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuruMapel;
use App\Models\PengampuKelas;
use App\Services\AcademicRelocationService;
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

        // Jika terdapat konflik dan tidak ada kelas yang berhasil ditambahkan
        if (count($conflictClasses) > 0 && $addedCount === 0) {
            $conflictDetails = collect($conflictClasses)->map(fn($c) => "{$c['kelas']} (diampu {$c['guru']})")->join(', ');
            return back()->with('error', "Gagal menugaskan: Mata pelajaran {$conflictClasses[0]['mapel']} di kelas {$conflictDetails} sudah memiliki guru pengampu. Dalam 1 kelas hanya diperbolehkan 1 guru untuk setiap mata pelajaran.");
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
     * Ganti Guru Pengampu pada suatu kelas atau Tukar Kelas (Swap) antar 2 guru (Solusi Studi Kasus 1).
     */
    public function reassign(Request $request, PengampuKelas $guruMapel, AcademicRelocationService $relocationService)
    {
        $validated = $request->validate([
            'target_guru_id'        => 'required|exists:gurus,id',
            'mode'                  => 'nullable|in:serah_terima,bawa_soal_reset_nilai',
            'swap_with_pengampu_id' => 'nullable|exists:pengampu_kelas,id',
        ]);

        try {
            $result = $relocationService->reassignOrSwapGuru(
                $guruMapel,
                (int) $validated['target_guru_id'],
                $validated['mode'] ?? 'serah_terima',
                !empty($validated['swap_with_pengampu_id']) ? (int) $validated['swap_with_pengampu_id'] : null
            );

            return back()->with('success', $result['message']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus penugasan guru pengampu mapel pada kelas tertentu.
     * Dilengkapi proteksi agar data tugas/kuis/ujian/nilai tidak ikut terhapus akibat human error.
     */
    public function destroy(Request $request, PengampuKelas $guruMapel, AcademicRelocationService $relocationService)
    {
        $summary = $relocationService->getPengampuActivitySummary($guruMapel);

        if ($summary['has_data'] && !$request->boolean('force_delete')) {
            $kelasNama = $guruMapel->kelas?->nama_kelas ?? 'Kelas';
            $mapelNama = $guruMapel->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
            $guruNama = $guruMapel->guruMapel?->guru?->nama ?? 'Guru';

            return back()->with(
                'error',
                "Penugasan {$guruNama} ({$mapelNama} - {$kelasNama}) tidak dapat dihapus langsung karena sudah memiliki {$summary['total_aktivitas']} aktivitas pembelajaran ({$summary['materi_count']} Materi, {$summary['tugas_count']} Tugas, {$summary['quiz_count']} Kuis, {$summary['ujian_count']} Ujian) dan {$summary['total_pengerjaan']} data nilai siswa. Silakan gunakan tombol 'Ganti / Tukar Guru' agar data nilai siswa tidak hilang!"
            );
        }

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

