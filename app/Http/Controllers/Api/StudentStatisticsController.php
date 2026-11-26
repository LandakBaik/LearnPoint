<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Services\RekapNilaiService;
use Illuminate\Http\Request;

class StudentStatisticsController extends Controller
{
    /**
     * Statistik nilai milik siswa yang sedang masuk.
     *
     * Statistik hanya dapat dibuka untuk periode akademik tempat siswa
     * tercatat sebagai anggota kelas. Dengan demikian siswa tidak dapat
     * meminta data periode milik siswa/kelas lain.
     */
    public function index(Request $request, RekapNilaiService $rekapNilaiService)
    {
        $request->validate([
            'academic_period_id' => ['nullable', 'integer', 'exists:academic_periods,id'],
        ]);

        $siswa = $request->user()->siswa;
        if (!$siswa) {
            return $this->notFoundResponse('Profil Siswa tidak ditemukan untuk akun ini.');
        }

        $periodIds = $siswa->anggotaKelases()
            ->whereNotNull('academic_period_id')
            ->pluck('academic_period_id')
            ->unique()
            ->values();

        $periods = AcademicPeriod::whereIn('id', $periodIds)
            ->orderByDesc('tahun_ajaran')
            ->orderByRaw("CASE WHEN semester = 'genap' THEN 2 ELSE 1 END DESC")
            ->get();

        $requestedPeriodId = $request->integer('academic_period_id');
        $activePeriod = AcademicPeriod::current();
        $selectedPeriod = $requestedPeriodId
            ? $periods->firstWhere('id', $requestedPeriodId)
            : ($periods->firstWhere('id', $activePeriod?->id) ?? $periods->first());

        if ($requestedPeriodId && !$selectedPeriod) {
            return $this->forbiddenResponse('Anda tidak terdaftar pada periode akademik yang dipilih.');
        }

        if (!$selectedPeriod) {
            return $this->successResponse([
                'academic_period' => null,
                'available_periods' => [],
                'evaluasi' => $rekapNilaiService->buildSiswaStatistik($siswa),
            ]);
        }

        $anggotaKelas = $siswa->anggotaKelases()
            ->where('academic_period_id', $selectedPeriod->id)
            ->first();

        $statistik = $rekapNilaiService->buildSiswaStatistik(
            $siswa,
            $anggotaKelas?->kelas_id,
            $selectedPeriod
        );

        return $this->successResponse([
            'academic_period' => [
                'id' => $selectedPeriod->id,
                'tahun_ajaran' => $selectedPeriod->tahun_ajaran,
                'semester' => ucfirst($selectedPeriod->semester),
                'tanggal_mulai' => $selectedPeriod->tanggal_mulai,
                'tanggal_selesai' => $selectedPeriod->tanggal_selesai,
            ],
            'available_periods' => $periods->map(fn (AcademicPeriod $period) => [
                'id' => $period->id,
                'tahun_ajaran' => $period->tahun_ajaran,
                'semester' => ucfirst($period->semester),
            ])->values(),
            'evaluasi' => $statistik,
        ]);
    }
}
