<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\PengampuKelas;
use App\Models\Quiz;
use App\Models\Tugas;
use App\Models\Ujian;
use App\Services\AcademicRelocationService;
use Illuminate\Http\Request;

class RelokasiAktivitasController extends Controller
{
    /**
     * Halaman Pusat Koreksi / Relokasi Mapel & Kelas untuk Guru (Solusi Studi Kasus 2).
     * Memungkinkan guru memindahkan atau menyalin Tugas, Kuis, Ujian, dan Materi
     * yang salah input antar mata pelajaran maupun antar kelas dengan aman.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $guruId = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guruId, 403, 'Akses khusus guru.');

        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', fn($q) => $q->where('guru_id', $guruId))
            ->get()
            ->sortBy(fn($pk) => ($pk->guruMapel?->mapel?->nama_mapel ?? '') . ' ' . ($pk->kelas?->nama_kelas ?? ''))
            ->values();

        // Ambil semua Tugas milik guru
        $tugases = Tugas::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
            ->withCount(['soals', 'nilais'])
            ->whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guruId))
            ->latest()
            ->get()
            ->map(fn(Tugas $t) => [
                'uid'               => 'tugas-' . $t->id,
                'id'                => $t->id,
                'jenis'             => 'tugas',
                'jenis_label'       => 'Tugas (' . ($t->tipe === 'pilihan_ganda' ? 'Pilihan Ganda' : 'Upload Berkas') . ')',
                'badge_color'       => 'indigo',
                'judul'             => $t->judul,
                'pengampu_kelas_id' => $t->pengampu_kelas_id,
                'mapel_id'          => $t->pengampuKelas?->guruMapel?->mapel_id,
                'mapel_nama'        => $t->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? '-',
                'kelas_id'          => $t->pengampuKelas?->kelas_id,
                'kelas_nama'        => $t->pengampuKelas?->kelas?->nama_kelas ?? '-',
                'tingkatan'         => $t->pengampuKelas?->kelas?->tingkatan ?? '-',
                'soal_count'        => $t->soals_count,
                'submission_count'  => $t->nilais_count,
                'created_at'        => $t->created_at?->format('d M Y, H:i'),
                'timestamp'         => $t->created_at?->timestamp ?? 0,
            ]);

        // Ambil semua Kuis milik guru
        $quizzes = Quiz::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
            ->withCount(['soals', 'nilais'])
            ->whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guruId))
            ->latest()
            ->get()
            ->map(fn(Quiz $q) => [
                'uid'               => 'quiz-' . $q->id,
                'id'                => $q->id,
                'jenis'             => 'quiz',
                'jenis_label'       => 'Kuis Petualangan',
                'badge_color'       => 'emerald',
                'judul'             => $q->judul,
                'pengampu_kelas_id' => $q->pengampu_kelas_id,
                'mapel_id'          => $q->pengampuKelas?->guruMapel?->mapel_id,
                'mapel_nama'        => $q->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? '-',
                'kelas_id'          => $q->pengampuKelas?->kelas_id,
                'kelas_nama'        => $q->pengampuKelas?->kelas?->nama_kelas ?? '-',
                'tingkatan'         => $q->pengampuKelas?->kelas?->tingkatan ?? '-',
                'soal_count'        => $q->soals_count,
                'submission_count'  => $q->nilais_count,
                'created_at'        => $q->created_at?->format('d M Y, H:i'),
                'timestamp'         => $q->created_at?->timestamp ?? 0,
            ]);

        // Ambil semua Ujian milik guru
        $ujians = Ujian::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
            ->withCount(['soals', 'seleksiUjians'])
            ->whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guruId))
            ->latest()
            ->get()
            ->map(fn(Ujian $u) => [
                'uid'               => 'ujian-' . $u->id,
                'id'                => $u->id,
                'jenis'             => 'ujian',
                'jenis_label'       => 'Ujian Evaluasi',
                'badge_color'       => 'amber',
                'judul'             => $u->judul,
                'pengampu_kelas_id' => $u->pengampu_kelas_id,
                'mapel_id'          => $u->pengampuKelas?->guruMapel?->mapel_id,
                'mapel_nama'        => $u->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? '-',
                'kelas_id'          => $u->pengampuKelas?->kelas_id,
                'kelas_nama'        => $u->pengampuKelas?->kelas?->nama_kelas ?? '-',
                'tingkatan'         => $u->pengampuKelas?->kelas?->tingkatan ?? '-',
                'soal_count'        => $u->soals_count,
                'submission_count'  => $u->seleksi_ujians_count,
                'created_at'        => $u->created_at?->format('d M Y, H:i'),
                'timestamp'         => $u->created_at?->timestamp ?? 0,
            ]);

        // Ambil semua Materi milik guru
        $materis = Materi::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
            ->whereHas('pengampuKelas.guruMapel', fn($q) => $q->where('guru_id', $guruId))
            ->latest()
            ->get()
            ->map(fn(Materi $m) => [
                'uid'               => 'materi-' . $m->id,
                'id'                => $m->id,
                'jenis'             => 'materi',
                'jenis_label'       => 'Materi Pembelajaran',
                'badge_color'       => 'blue',
                'judul'             => $m->judul,
                'pengampu_kelas_id' => $m->pengampu_kelas_id,
                'mapel_id'          => $m->pengampuKelas?->guruMapel?->mapel_id,
                'mapel_nama'        => $m->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? '-',
                'kelas_id'          => $m->pengampuKelas?->kelas_id,
                'kelas_nama'        => $m->pengampuKelas?->kelas?->nama_kelas ?? '-',
                'tingkatan'         => $m->pengampuKelas?->kelas?->tingkatan ?? '-',
                'soal_count'        => 0,
                'submission_count'  => 0,
                'created_at'        => $m->created_at?->format('d M Y, H:i'),
                'timestamp'         => $m->created_at?->timestamp ?? 0,
            ]);

        $allActivities = collect()
            ->concat($tugases)
            ->concat($quizzes)
            ->concat($ujians)
            ->concat($materis)
            ->sortByDesc('timestamp')
            ->values();

        $targetOptions = $pengampuKelases->map(fn($pk) => [
            'id'         => $pk->id,
            'mapel_id'   => $pk->guruMapel?->mapel_id,
            'mapel_nama' => $pk->guruMapel?->mapel?->nama_mapel ?? 'Mapel',
            'kelas_id'   => $pk->kelas_id,
            'kelas_nama' => $pk->kelas?->nama_kelas ?? 'Kelas',
            'tingkatan'  => $pk->kelas?->tingkatan ?? '-',
            'label'      => ($pk->guruMapel?->mapel?->nama_mapel ?? 'Mapel') . ' — Kelas ' . ($pk->kelas?->nama_kelas ?? '-'),
        ])->values();

        return view('guru.relokasi.index', [
            'allActivities'   => $allActivities,
            'pengampuKelases' => $pengampuKelases,
            'targetOptions'   => $targetOptions,
            'counts'          => [
                'total'  => $allActivities->count(),
                'tugas'  => $tugases->count(),
                'quiz'   => $quizzes->count(),
                'ujian'  => $ujians->count(),
                'materi' => $materis->count(),
            ],
        ]);
    }

    /**
     * Eksekusi pemindahan atau penyalinan aktivitas antar Mapel / Kelas.
     */
    public function relocate(Request $request, AcademicRelocationService $relocationService)
    {
        $user = $request->user();
        $guruId = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guruId, 403, 'Akses khusus guru.');

        $validated = $request->validate([
            'jenis'                    => 'required|in:tugas,quiz,ujian,materi',
            'aktivitas_id'             => 'required|integer',
            'target_pengampu_kelas_id' => 'required|exists:pengampu_kelas,id',
            'aksi'                     => 'required|in:pindah_aman,pindah_reset,salin',
        ]);

        $targetPk = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->findOrFail($validated['target_pengampu_kelas_id']);

        try {
            $result = $relocationService->relocateActivity(
                $validated['jenis'],
                (int) $validated['aktivitas_id'],
                $targetPk,
                $validated['aksi'],
                (int) $guruId
            );

            return back()->with('success', $result['message']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

