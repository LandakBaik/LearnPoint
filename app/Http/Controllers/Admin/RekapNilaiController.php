<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Services\RekapNilaiService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapNilaiController extends Controller
{
    private const FILTER_KEYS = [
        'academic_period_id',
        'kelas_id',
        'mapel_id',
        'status',
        'search',
        'mode',
        'hitung_kosong',
        'sort_by',
        'sort_dir',
    ];

    public function __construct(
        protected RekapNilaiService $rekapNilaiService
    ) {}

    /**
     * Halaman utama Perekapan Nilai Keseluruhan untuk Admin / Operator.
     */
    public function index(Request $request)
    {
        $data = $this->rekapNilaiService->buildAdminRekap($request->only(self::FILTER_KEYS));

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $allFilteredRows = $data['rows'];
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedRows = new LengthAwarePaginator(
            $allFilteredRows->forPage($currentPage, $perPage)->values(),
            $allFilteredRows->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $data['filteredTotal'] = $allFilteredRows->count();
        $data['rows'] = $paginatedRows;
        $data['perPage'] = $perPage;

        return view('admin.rekap_nilai.index', $data);
    }

    /**
     * Halaman detail transkrip nilai per siswa (rincian per mapel & tiap tugas/kuis/ujian).
     */
    public function showSiswa(Request $request, Siswa $siswa)
    {
        $periodId = $request->filled('academic_period_id')
            ? (int) $request->input('academic_period_id')
            : null;
        $hitungKosong = (string) $request->input('hitung_kosong', 'abaikan');

        $data = $this->rekapNilaiService->buildSiswaDetail($siswa, $periodId, $hitungKosong, 'rata');

        $returnFilters = array_filter(
            $request->only(array_merge(self::FILTER_KEYS, ['per_page', 'page'])),
            fn ($v) => $v !== null && $v !== ''
        );
        if (!isset($returnFilters['academic_period_id']) && $data['selectedPeriodId']) {
            $returnFilters['academic_period_id'] = $data['selectedPeriodId'];
        }
        if (!isset($returnFilters['kelas_id']) && $data['kelas']?->id) {
            $returnFilters['kelas_id'] = $data['kelas']->id;
        }

        $data['returnFilters'] = $returnFilters;

        return view('admin.rekap_nilai.detail_siswa', $data);
    }

    /**
     * Export Perekapan Nilai Keseluruhan ke PDF (Ringkasan atau Leger Matriks Mapel).
     */
    public function exportPdf(Request $request)
    {
        $data = $this->rekapNilaiService->buildAdminRekap($request->only(self::FILTER_KEYS));

        $periodSlug = Str::slug(
            ($data['selectedPeriod']?->tahun_ajaran ?? 'periode') . '-' . ($data['selectedPeriod']?->semester ?? '')
        );
        $kelasSlug = $data['selectedKelas'] ? Str::slug($data['selectedKelas']->nama_kelas) : 'semua-kelas';
        $modeLabel = $data['mode'] === 'leger' ? 'Leger-Nilai' : 'Rekap-Nilai-Keseluruhan';

        $filename = "{$modeLabel}-{$kelasSlug}-{$periodSlug}.pdf";
        $orientation = 'landscape';

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.rekap_nilai.export_pdf', $data)
                ->setPaper('a4', $orientation);

            return $pdf->download($filename);
        }

        return view('admin.rekap_nilai.export_pdf', array_merge($data, ['autoPrint' => true]));
    }

    /**
     * Export Perekapan Nilai Keseluruhan ke CSV (UTF-8 BOM untuk Microsoft Excel).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $data = $this->rekapNilaiService->buildAdminRekap($request->only(self::FILTER_KEYS));

        $periodSlug = Str::slug(
            ($data['selectedPeriod']?->tahun_ajaran ?? 'periode') . '-' . ($data['selectedPeriod']?->semester ?? '')
        );
        $kelasSlug = $data['selectedKelas'] ? Str::slug($data['selectedKelas']->nama_kelas) : 'semua-kelas';
        $modeLabel = $data['mode'] === 'leger' ? 'Leger-Nilai' : 'Rekap-Nilai-Keseluruhan';
        $filename = "{$modeLabel}-{$kelasSlug}-{$periodSlug}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM agar Excel membaca karakter dengan benar
            fwrite($handle, "\xEF\xBB\xBF");

            $safe = static function ($value): string {
                $str = (string) $value;
                if ($str !== '' && in_array($str[0], ['=', '+', '-', '@'], true) && !is_numeric($str)) {
                    return "'" . $str;
                }

                return $str;
            };

            if ($data['mode'] === 'leger' && $data['legerMapels']->isNotEmpty()) {
                $headerRow = ['No', 'Peringkat', 'NIS', 'Nama Siswa', 'Kelas'];
                foreach ($data['legerMapels'] as $pk) {
                    $headerRow[] = ($pk->guruMapel?->mapel?->nama_mapel ?? 'Mapel') . ' (KKM ' . (int) ($pk->guruMapel?->mapel?->kkm ?? 75) . ')';
                }
                $headerRow[] = 'Rata-rata Akhir';
                $headerRow[] = 'Mapel Tuntas';
                $headerRow[] = 'Status';
                fputcsv($handle, $headerRow);

                foreach ($data['rows'] as $index => $row) {
                    $csvRow = [
                        $index + 1,
                        $row['peringkat'] ?? '-',
                        $safe($row['nis']),
                        $safe($row['nama_siswa']),
                        $safe($row['nama_kelas']),
                    ];

                    foreach ($data['legerMapels'] as $pk) {
                        $mId = $pk->guruMapel->mapel_id;
                        $cell = $row['mapel_scores'][$mId] ?? null;
                        if (!$cell || !$cell['accessible']) {
                            $csvRow[] = 'N/A';
                        } elseif ($cell['nilai_akhir'] === null) {
                            $csvRow[] = '-';
                        } else {
                            $csvRow[] = number_format($cell['nilai_akhir'], 2, '.', '');
                        }
                    }

                    $csvRow[] = $row['nilai_akhir'] !== null ? number_format($row['nilai_akhir'], 2, '.', '') : '-';
                    $csvRow[] = "{$row['mapel_tuntas']}/{$row['mapel_wajib']}";
                    $csvRow[] = match ($row['status_ketuntasan']) {
                        'tuntas' => 'Tuntas',
                        'belum_tuntas' => 'Belum Tuntas',
                        default => 'Belum Ada Nilai',
                    };

                    fputcsv($handle, $csvRow);
                }
            } else {
                fputcsv($handle, [
                    'No',
                    'Peringkat',
                    'NIS',
                    'Nama Siswa',
                    'Kelas',
                    'Rata-rata Tugas',
                    'Tugas Dinilai',
                    'Rata-rata Kuis',
                    'Kuis Selesai',
                    'Rata-rata Ujian',
                    'Ujian Selesai',
                    'Nilai Akhir Keseluruhan',
                    'Mapel Tuntas',
                    'Mapel Belum Tuntas',
                    'Status Ketuntasan',
                ]);

                foreach ($data['rows'] as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row['peringkat'] ?? '-',
                        $safe($row['nis']),
                        $safe($row['nama_siswa']),
                        $safe($row['nama_kelas']),
                        $row['rata_tugas'] !== null ? number_format($row['rata_tugas'], 2, '.', '') : '-',
                        "{$row['tugas_dinilai']}/{$row['tugas_total']}",
                        $row['rata_kuis'] !== null ? number_format($row['rata_kuis'], 2, '.', '') : '-',
                        "{$row['kuis_dinilai']}/{$row['kuis_total']}",
                        $row['rata_ujian'] !== null ? number_format($row['rata_ujian'], 2, '.', '') : '-',
                        "{$row['ujian_dinilai']}/{$row['ujian_total']}",
                        $row['nilai_akhir'] !== null ? number_format($row['nilai_akhir'], 2, '.', '') : '-',
                        "{$row['mapel_tuntas']}/{$row['mapel_wajib']}",
                        !empty($row['mapel_belum_tuntas_list']) ? $safe(implode(', ', $row['mapel_belum_tuntas_list'])) : '-',
                        match ($row['status_ketuntasan']) {
                            'tuntas' => 'Tuntas',
                            'belum_tuntas' => 'Belum Tuntas',
                            default => 'Belum Ada Nilai',
                        },
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export Transkrip Nilai Detail Siswa ke PDF.
     */
    public function exportSiswaPdf(Request $request, Siswa $siswa)
    {
        $periodId = $request->filled('academic_period_id')
            ? (int) $request->input('academic_period_id')
            : null;
        $hitungKosong = (string) $request->input('hitung_kosong', 'abaikan');

        $data = $this->rekapNilaiService->buildSiswaDetail($siswa, $periodId, $hitungKosong, 'rata');

        $namaSlug = Str::slug($siswa->nama_siswa ?? ('siswa-' . $siswa->id));
        $periodSlug = Str::slug(
            ($data['selectedPeriod']?->tahun_ajaran ?? 'periode') . '-' . ($data['selectedPeriod']?->semester ?? '')
        );
        $filename = "Transkrip-Nilai-{$namaSlug}-{$periodSlug}.pdf";

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.rekap_nilai.siswa_pdf', $data)
                ->setPaper('a4', 'portrait');

            return $pdf->download($filename);
        }

        return view('admin.rekap_nilai.siswa_pdf', array_merge($data, ['autoPrint' => true]));
    }
}
