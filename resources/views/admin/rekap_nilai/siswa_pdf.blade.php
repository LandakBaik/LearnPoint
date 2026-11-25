<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transkrip Nilai Siswa - {{ $siswa->nama_siswa ?? 'Siswa' }}</title>
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
        .hijau { color: #059669; font-weight: bold; }
        .merah { color: #dc2626; font-weight: bold; }
        .abu { color: #64748b; }
        .summary-box {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 10px 12px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body @if(!empty($autoPrint)) onload="window.print()" @endif>
    <div class="kop">
        <h1>SISTEM E-LEARNING SEKOLAH — LEARNPOINT</h1>
        <h2>TRANSKRIP REKAPITULASI NILAI KESELURUHAN SISWA</h2>
        <p>Periode Akademik: {{ $selectedPeriod?->label ?? '-' }} | Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</p>
    </div>

    <table class="meta">
        <tr>
            <td style="width: 18%;" class="font-bold">Nama Siswa</td>
            <td style="width: 34%;">: {{ $siswa->nama_siswa ?? $siswa->user?->name ?? '-' }}</td>
            <td style="width: 18%;" class="font-bold">Kelas</td>
            <td style="width: 30%;">: {{ $kelas?->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold">NIS</td>
            <td>: {{ $siswa->nis ?? '-' }}</td>
            <td class="font-bold">Wali Kelas</td>
            <td>: {{ $kelas?->guru?->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold">Agama</td>
            <td>: {{ $siswa->agama ?? '-' }}</td>
            <td class="font-bold">Periode</td>
            <td>: {{ $selectedPeriod?->label ?? '-' }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th class="text-left">Mata Pelajaran</th>
                <th class="text-left" style="width: 120px;">Guru Pengampu</th>
                <th style="width: 38px;">KKM</th>
                <th style="width: 52px;">Tugas</th>
                <th style="width: 52px;">Kuis</th>
                <th style="width: 52px;">Ujian</th>
                <th style="width: 58px;">Akhir</th>
                <th style="width: 36px;">Pred</th>
                <th style="width: 68px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailMapels as $i => $dm)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-left font-bold">{{ $dm['nama_mapel'] }}</td>
                    <td class="text-left">{{ $dm['nama_guru'] }}</td>
                    <td class="text-center">{{ (int) $dm['kkm'] }}</td>
                    <td class="text-center">{{ $dm['rata_tugas'] !== null ? number_format($dm['rata_tugas'], 1) : '-' }}</td>
                    <td class="text-center">{{ $dm['rata_kuis'] !== null ? number_format($dm['rata_kuis'], 1) : '-' }}</td>
                    <td class="text-center">{{ $dm['rata_ujian'] !== null ? number_format($dm['rata_ujian'], 1) : '-' }}</td>
                    <td class="text-center font-bold {{ $dm['status'] === 'tuntas' ? 'hijau' : ($dm['status'] === 'belum_tuntas' ? 'merah' : 'abu') }}">
                        {{ $dm['nilai_akhir'] !== null ? number_format($dm['nilai_akhir'], 2) : '-' }}
                    </td>
                    <td class="text-center font-bold">{{ $dm['predikat'] }}</td>
                    <td class="text-center">
                        @if($dm['status'] === 'tuntas')
                            <span class="hijau">Tuntas</span>
                        @elseif($dm['status'] === 'belum_tuntas')
                            <span class="merah">Belum</span>
                        @else
                            <span class="abu">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center abu">Belum ada mata pelajaran pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <strong>Ringkasan Capaian:</strong>
        Rata-rata Keseluruhan: <strong>{{ $summary['rata_keseluruhan'] !== null ? number_format($summary['rata_keseluruhan'], 2) : '-' }}</strong>
        (Predikat <strong>{{ $summary['predikat'] }}</strong>) |
        Mapel Tuntas: <strong>{{ $summary['mapel_tuntas'] }}/{{ $summary['total_mapel'] }}</strong> |
        Belum Tuntas: <strong>{{ $summary['mapel_belum_tuntas'] }}</strong>
    </div>
</body>
</html>
