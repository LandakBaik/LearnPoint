<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Aktivitas Guru - {{ $guru->nama ?? 'Guru' }}</title>
    <style>
        @page { margin: 16mm 14mm; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .kop {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .kop h1 { font-size: 15px; margin: 0; text-transform: uppercase; }
        .kop h2 { font-size: 12px; margin: 3px 0 2px; color: #334155; }
        .kop p { font-size: 9.5px; margin: 0; color: #64748b; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .meta td { padding: 3px 4px; vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.data th, table.data td {
            border: 1px solid #94a3b8;
            padding: 6px 5px;
            vertical-align: middle;
        }
        table.data th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .abu { color: #64748b; }
        .summary-box {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 10px 12px;
        }
    </style>
</head>
<body @if(!empty($autoPrint)) onload="window.print()" @endif>
    <div class="kop">
        <h1>SISTEM E-LEARNING SEKOLAH — LEARNPOINT</h1>
        <h2>LAPORAN DETAIL AKTIVITAS MENGAJAR GURU</h2>
        <p>Periode Akademik: {{ $selectedPeriod?->label ?? '-' }} | Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</p>
    </div>

    <table class="meta">
        <tr>
            <td style="width: 18%;" class="font-bold">Nama Guru</td>
            <td style="width: 34%;">: {{ $guru->nama ?? $guru->user?->name ?? '-' }}</td>
            <td style="width: 18%;" class="font-bold">Wali Kelas</td>
            <td style="width: 30%;">: {{ $guru->kelas?->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold">NUPTK</td>
            <td>: {{ $guru->nuptk ?? '-' }}</td>
            <td class="font-bold">Periode</td>
            <td>: {{ $selectedPeriod?->label ?? '-' }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th class="text-left">Mata Pelajaran</th>
                <th style="width: 70px;">Kelas</th>
                <th style="width: 55px;">Materi</th>
                <th style="width: 55px;">Tugas</th>
                <th style="width: 65px;">Kuis (Pub/Tot)</th>
                <th style="width: 65px;">Ujian (Akt/Tot)</th>
                <th style="width: 75px;">Tugas Menunggu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailMapels as $i => $dm)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-left font-bold">{{ $dm['nama_mapel'] }}</td>
                    <td class="text-left">{{ $dm['kelas_list']->implode(', ') }}</td>
                    <td class="text-center font-bold">{{ $dm['jumlah_materi'] }}</td>
                    <td class="text-center font-bold">{{ $dm['jumlah_tugas'] }}</td>
                    <td class="text-center">{{ $dm['jumlah_kuis_published'] }}/{{ $dm['jumlah_kuis'] }}</td>
                    <td class="text-center">{{ $dm['jumlah_ujian_aktif'] }}/{{ $dm['jumlah_ujian'] }}</td>
                    <td class="text-center">{{ $dm['tugas_menunggu_dinilai'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center abu">Belum ada penugasan mata pelajaran pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <strong>Ringkasan Total:</strong>
        Materi: <strong>{{ $summary['total_materi'] }}</strong> |
        Tugas: <strong>{{ $summary['total_tugas'] }}</strong> |
        Kuis Published: <strong>{{ $summary['total_kuis_published'] }}/{{ $summary['total_kuis'] }}</strong> |
        Ujian Aktif: <strong>{{ $summary['total_ujian_aktif'] }}/{{ $summary['total_ujian'] }}</strong> |
        Total Aktivitas: <strong>{{ $summary['total_aktivitas'] }}</strong>
    </div>
</body>
</html>
