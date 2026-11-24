<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Services\MonitoringGuruService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonitoringGuruController extends Controller
{
    public function __construct(
        protected MonitoringGuruService $monitoringGuruService
    ) {}

    /**
     * Halaman utama Perekapan & Monitoring Aktivitas Guru untuk Admin / Operator.
     */
    public function index(Request $request)
    {
        $data = $this->monitoringGuruService->buildAdminMonitoring($request->only([
            'academic_period_id',
            'kelas_id',
            'mapel_id',
            'keaktifan',
            'search',
        ]));

        return view('admin.monitoring_guru.index', $data);
    }

    /**
     * Halaman detail aktivitas mengajar untuk satu guru tertentu.
     */
    public function showGuru(Request $request, Guru $guru)
    {
        $periodId = $request->filled('academic_period_id')
            ? (int) $request->input('academic_period_id')
            : null;

        $data = $this->monitoringGuruService->buildGuruDetail($guru, $periodId);

        return view('admin.monitoring_guru.detail_guru', $data);
    }

    /**
     * Export Laporan Monitoring Aktivitas Guru ke PDF.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->monitoringGuruService->buildAdminMonitoring($request->only([
            'academic_period_id',
            'kelas_id',
            'mapel_id',
            'keaktifan',
            'search',
        ]));

        $periodSlug = Str::slug(
            ($data['selectedPeriod']?->tahun_ajaran ?? 'periode') . '-' . ($data['selectedPeriod']?->semester ?? '')
        );
        $filename = "Monitoring-Aktivitas-Guru-{$periodSlug}.pdf";

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.monitoring_guru.export_pdf', $data)
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename);
        }

        return view('admin.monitoring_guru.export_pdf', array_merge($data, ['autoPrint' => true]));
    }

    /**
     * Export Laporan Monitoring Aktivitas Guru ke CSV (UTF-8 BOM untuk Excel).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $data = $this->monitoringGuruService->buildAdminMonitoring($request->only([
            'academic_period_id',
            'kelas_id',
            'mapel_id',
            'keaktifan',
            'search',
        ]));

        $periodSlug = Str::slug(
            ($data['selectedPeriod']?->tahun_ajaran ?? 'periode') . '-' . ($data['selectedPeriod']?->semester ?? '')
        );
        $filename = "Monitoring-Aktivitas-Guru-{$periodSlug}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'NUPTK',
                'Nama Guru',
                'Wali Kelas',
                'Mata Pelajaran',
                'Jumlah Kelas',
                'Materi',
                'Tugas',
                'Kuis (Published/Total)',
                'Ujian (Aktif/Total)',
                'Total Aktivitas',
                'Tugas Dinilai',
                'Tugas Menunggu Dinilai',
                'Essay Belum Dikoreksi',
                'Keaktifan',
                'Aktivitas Terakhir',
            ]);

            foreach ($data['rows'] as $i => $row) {
                $keaktifanLabel = match ($row['kategori_keaktifan']) {
                    'sangat_aktif' => 'Sangat Aktif',
                    'aktif' => 'Aktif',
                    'kurang_aktif' => 'Kurang Aktif',
                    default => 'Belum Aktif',
                };

                fputcsv($handle, [
                    $i + 1,
                    $row['nuptk'],
                    $row['nama_guru'],
                    $row['wali_kelas'] ?? '-',
                    implode(', ', $row['mapel_list']) ?: '-',
                    $row['jumlah_kelas'],
                    $row['jumlah_materi'],
                    $row['jumlah_tugas'],
                    "{$row['jumlah_kuis_published']}/{$row['jumlah_kuis_total']}",
                    "{$row['jumlah_ujian_aktif']}/{$row['jumlah_ujian_total']}",
                    $row['total_aktivitas'],
                    $row['tugas_sudah_dinilai'],
                    $row['tugas_menunggu_dinilai'],
                    $row['essay_menunggu_koreksi'],
                    $keaktifanLabel,
                    $row['aktivitas_terakhir'] ? $row['aktivitas_terakhir']->format('d/m/Y H:i') : '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export Laporan Detail Aktivitas 1 Guru ke PDF.
     */
    public function exportGuruPdf(Request $request, Guru $guru)
    {
        $periodId = $request->filled('academic_period_id')
            ? (int) $request->input('academic_period_id')
            : null;

        $data = $this->monitoringGuruService->buildGuruDetail($guru, $periodId);

        $namaSlug = Str::slug($guru->nama ?? ('guru-' . $guru->id));
        $periodSlug = Str::slug(
            ($data['selectedPeriod']?->tahun_ajaran ?? 'periode') . '-' . ($data['selectedPeriod']?->semester ?? '')
        );
        $filename = "Laporan-Aktivitas-Guru-{$namaSlug}-{$periodSlug}.pdf";

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.monitoring_guru.guru_pdf', $data)
                ->setPaper('a4', 'portrait');

            return $pdf->download($filename);
        }

        return view('admin.monitoring_guru.guru_pdf', array_merge($data, ['autoPrint' => true]));
    }
}
