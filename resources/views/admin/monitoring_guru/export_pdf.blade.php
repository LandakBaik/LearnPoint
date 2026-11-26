<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring Aktivitas Guru - LearnPoint</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .kop {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop h1 { font-size: 14px; margin: 0; text-transform: uppercase; }
        .kop h2 { font-size: 12px; margin: 3px 0 2px; color: #334155; }
        .kop p { font-size: 9.5px; margin: 0; color: #64748b; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .meta td { padding: 2px 4px; vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data th, table.data td {
            border: 1px solid #94a3b8;
            padding: 4px 5px;
            vertical-align: middle;
        }
        table.data th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .hijau { color: #059669; font-weight: bold; }
        .biru { color: #1d4ed8; font-weight: bold; }
        .kuning { color: #b45309; font-weight: bold; }
        .merah { color: #dc2626; font-weight: bold; }
        .abu { color: #64748b; }
        .footer-note { font-size: 9px; color: #475569; margin-top: 6px; }
    </style>
</head>
<body @if(!empty($autoPrint)) onload="window.print()" @endif>
    <div class="kop">
        <h1>SISTEM E-LEARNING SEKOLAH — LEARNPOINT</h1>
        <h2>LAPORAN REKAPITULASI &amp; MONITORING AKTIVITAS GURU</h2>
        <p>Periode Akademik: {{ $selectedPeriod?->label ?? '-' }} | Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</p>
    </div>

    <table class="meta">
        <tr>
            <td style="width: 15%;" class="font-bold">Total Guru</td>
            <td style="width: 35%;">: {{ $rows->count() }} guru (Aktif: {{ $summary['guru_aktif'] }}, Belum Aktif: {{ $summary['guru_belum_aktif'] }})</td>
            <td style="width: 18%;" class="font-bold">Total Materi / Tugas</td>
            <td style="width: 32%;">: {{ $summary['total_materi'] }} Materi / {{ $summary['total_tugas'] }} Tugas</td>
        </tr>
        <tr>
            <td class="font-bold">Filter Kelas / Mapel</td>
            <td>: {{ $selectedKelas ? $selectedKelas->nama_kelas : 'Semua Kelas' }} / {{ $selectedMapel ? $selectedMapel->nama_mapel : 'Semua Mapel' }}</td>
            <td class="font-bold">Total Kuis / Ujian</td>
            <td>: {{ $summary['total_kuis_published'] }} Kuis Published / {{ $summary['total_ujian_aktif'] }} Ujian Aktif</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 26px;">No</th>
                <th style="width: 82px;">NUPTK</th>
                <th class="text-left">Nama Guru</th>
                <th class="text-left" style="width: 135px;">Mapel Diampu</th>
                <th style="width: 38px;">Kls</th>
                <th style="width: 44px;">Materi</th>
                <th style="width: 44px;">Tugas</th>
                <th style="width: 52px;">Kuis</th>
                <th style="width: 52px;">Ujian</th>
                <th style="width: 58px;">Menunggu</th>
                <th style="width: 44px;">Total</th>
                <th style="width: 72px;">Keaktifan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $row['nuptk'] }}</td>
                    <td class="text-left font-bold">{{ $row['nama_guru'] }}</td>
                    <td class="text-left">{{ implode(', ', $row['mapel_list']) ?: '-' }}</td>
                    <td class="text-center">{{ $row['jumlah_kelas'] }}</td>
                    <td class="text-center font-bold">{{ $row['jumlah_materi'] }}</td>
                    <td class="text-center font-bold">{{ $row['jumlah_tugas'] }}</td>
                    <td class="text-center">{{ $row['jumlah_kuis_published'] }}/{{ $row['jumlah_kuis_total'] }}</td>
                    <td class="text-center">{{ $row['jumlah_ujian_aktif'] }}/{{ $row['jumlah_ujian_total'] }}</td>
                    <td class="text-center">
                        {{ $row['tugas_menunggu_dinilai'] }} tgs / {{ $row['essay_menunggu_koreksi'] }} esy
                    </td>
                    <td class="text-center font-bold">{{ $row['total_aktivitas'] }}</td>
                    <td class="text-center">
                        @if($row['kategori_keaktifan'] === 'sangat_aktif')
                            <span class="hijau">Sangat Aktif</span>
                        @elseif($row['kategori_keaktifan'] === 'aktif')
                            <span class="biru">Aktif</span>
                        @elseif($row['kategori_keaktifan'] === 'kurang_aktif')
                            <span class="kuning">Kurang Aktif</span>
                        @else
                            <span class="merah">Belum Aktif</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center abu">Tidak ada data guru.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        <strong>Keterangan:</strong> Total Aktivitas = Materi + Tugas + Kuis Published + Ujian Aktif/Selesai.
        Kategori Keaktifan dihitung relatif terhadap rata-rata keseluruhan guru ({{ number_format($ambangKeaktifan['rata_rata'] ?? 0, 1) }} aktivitas):
        Sangat Aktif (&gt; +25% di atas rata-rata), Aktif (sekitar rata-rata ±25%), Kurang Aktif (&lt; -25% di bawah rata-rata), Belum Aktif (0).
        Kolom Menunggu menunjukkan jumlah pengumpulan tugas (tgs) dan jawaban essay ujian (esy) yang belum dikoreksi guru.
    </div>
</body>
</html>
