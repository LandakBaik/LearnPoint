@php
    $isDenseLeger = $mode === 'leger' && $legerMapels->count() > 10;
    $isVeryDenseLeger = $mode === 'leger' && $legerMapels->count() > 14;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai Guru - LearnPoint</title>
    <style>
        @page { margin: {{ $isDenseLeger ? '10mm 8mm' : '14mm 12mm' }}; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: {{ $isVeryDenseLeger ? '8px' : ($isDenseLeger ? '9px' : '10px') }};
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
        .kop h1 { font-size: 14px; margin: 0; text-transform: uppercase; letter-spacing: 0.4px; }
        .kop h2 { font-size: 12px; margin: 3px 0 2px; color: #334155; }
        .kop p { font-size: 9.5px; margin: 0; color: #64748b; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9.5px; }
        .meta td { padding: 2px 4px; vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; table-layout: {{ $isDenseLeger ? 'fixed' : 'auto' }}; }
        table.data th, table.data td {
            border: 1px solid #94a3b8;
            padding: {{ $isVeryDenseLeger ? '3px 2px' : ($isDenseLeger ? '3px 3px' : '4px 5px') }};
            vertical-align: middle;
            word-wrap: break-word;
        }
        table.data th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            font-size: {{ $isVeryDenseLeger ? '7.5px' : ($isDenseLeger ? '8px' : '9px') }};
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .hijau { color: #059669; font-weight: bold; }
        .merah { color: #dc2626; font-weight: bold; }
        .abu { color: #64748b; }
        .footer-note { font-size: 8.5px; color: #475569; margin-top: 6px; line-height: 1.45; }
    </style>
</head>
<body @if(!empty($autoPrint)) onload="window.print()" @endif>
    <div class="kop">
        <h1>SISTEM E-LEARNING SEKOLAH — LEARNPOINT</h1>
        <h2>
            {{ $mode === 'leger' ? 'LEGER PEREKAPAN NILAI MAPEL DIAMPU PER KELAS' : 'LAPORAN PEREKAPAN NILAI SISWA (GURU PENGAMPU)' }}
        </h2>
        <p>Periode Akademik: {{ $selectedPeriod?->label ?? '-' }} | Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</p>
    </div>

    <table class="meta">
        <tr>
            <td style="width: 16%;" class="font-bold">Guru Pengampu</td>
            <td style="width: 34%;">: {{ $guru->nama ?? auth()->user()?->name ?? '-' }}</td>
            <td style="width: 18%;" class="font-bold">Total Siswa</td>
            <td style="width: 32%;">: {{ $rows->count() }} siswa (Tuntas: {{ $summary['jumlah_tuntas'] }}, Belum: {{ $summary['jumlah_belum_tuntas'] }})</td>
        </tr>
        <tr>
            <td class="font-bold">Kelas</td>
            <td>: {{ $selectedKelas ? $selectedKelas->nama_kelas : 'Semua Kelas Diampu' }}</td>
            <td class="font-bold">Rata-rata Nilai</td>
            <td>: {{ $summary['rata_keseluruhan'] !== null ? number_format($summary['rata_keseluruhan'], 2) : '-' }} (Ketuntasan: {{ $summary['persen_tuntas'] }}%)</td>
        </tr>
        <tr>
            <td class="font-bold">Mata Pelajaran</td>
            <td colspan="3">: {{ $selectedMapel ? $selectedMapel->nama_mapel . ' (KKM ' . (int) ($selectedMapel->kkm ?? 75) . ')' : 'Semua Mata Pelajaran Diampu' }}</td>
        </tr>
    </table>

    @if($mode === 'leger' && $legerMapels->isNotEmpty())
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 28px;">No</th>
                    <th style="width: 32px;">Rank</th>
                    <th style="width: 70px;">NIS</th>
                    <th class="text-left" style="width: 135px;">Nama Siswa</th>
                    @foreach($legerMapels as $pk)
                        <th>
                            {{ $pk->guruMapel->mapel->nama_mapel }}<br>
                            <span style="font-weight: normal; font-size: 8px;">(KKM {{ (int) ($pk->guruMapel->mapel->kkm ?? 75) }})</span>
                        </th>
                    @endforeach
                    <th style="width: 52px;">Rata2</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="text-center font-bold">{{ $row['peringkat'] ?? '-' }}</td>
                        <td class="text-center">{{ $row['nis'] }}</td>
                        <td class="text-left font-bold">{{ $row['nama_siswa'] }}</td>
                        @foreach($legerMapels as $pk)
                            @php $cell = $row['mapel_scores'][$pk->guruMapel->mapel_id] ?? null; @endphp
                            <td class="text-center">
                                @if(!$cell || !$cell['accessible'])
                                    <span class="abu">N/A</span>
                                @elseif($cell['nilai_akhir'] === null)
                                    <span class="abu">-</span>
                                @else
                                    <span class="{{ $cell['tuntas'] ? 'hijau' : 'merah' }}">
                                        {{ number_format($cell['nilai_akhir'], 1) }}
                                    </span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-center font-bold">
                            {{ $row['nilai_akhir'] !== null ? number_format($row['nilai_akhir'], 2) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 5 + $legerMapels->count() }}" class="text-center abu">Tidak ada data nilai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 28px;">No</th>
                    <th style="width: 34px;">Rank</th>
                    <th style="width: 75px;">NIS</th>
                    <th class="text-left">Nama Siswa</th>
                    <th style="width: 70px;">Kelas</th>
                    <th style="width: 68px;">Rata Tugas</th>
                    <th style="width: 68px;">Rata Kuis</th>
                    <th style="width: 68px;">Rata Ujian</th>
                    <th style="width: 68px;">Nilai Akhir</th>
                    <th style="width: 82px;">Ketuntasan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="text-center font-bold">{{ $row['peringkat'] ?? '-' }}</td>
                        <td class="text-center">{{ $row['nis'] }}</td>
                        <td class="text-left font-bold">{{ $row['nama_siswa'] }}</td>
                        <td class="text-center">{{ $row['nama_kelas'] }}</td>
                        <td class="text-center">
                            {{ $row['rata_tugas'] !== null ? number_format($row['rata_tugas'], 2) : '-' }}
                            <div class="abu" style="font-size: 8px;">({{ $row['tugas_dinilai'] }}/{{ $row['tugas_total'] }})</div>
                        </td>
                        <td class="text-center">
                            {{ $row['rata_kuis'] !== null ? number_format($row['rata_kuis'], 2) : '-' }}
                            <div class="abu" style="font-size: 8px;">({{ $row['kuis_dinilai'] }}/{{ $row['kuis_total'] }})</div>
                        </td>
                        <td class="text-center">
                            {{ $row['rata_ujian'] !== null ? number_format($row['rata_ujian'], 2) : '-' }}
                            <div class="abu" style="font-size: 8px;">({{ $row['ujian_dinilai'] }}/{{ $row['ujian_total'] }})</div>
                        </td>
                        <td class="text-center font-bold {{ $row['status_ketuntasan'] === 'tuntas' ? 'hijau' : ($row['status_ketuntasan'] === 'belum_tuntas' ? 'merah' : 'abu') }}">
                            {{ $row['nilai_akhir'] !== null ? number_format($row['nilai_akhir'], 2) : '-' }}
                        </td>
                        <td class="text-center">
                            @if($row['status_ketuntasan'] === 'tuntas')
                                <span class="hijau">Tuntas</span>
                            @elseif($row['status_ketuntasan'] === 'belum_tuntas')
                                <span class="merah">Belum Tuntas</span>
                            @else
                                <span class="abu">Belum Ada</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center abu">Tidak ada data nilai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer-note">
        <strong>Keterangan:</strong>
        Rekap ini hanya mencakup mata pelajaran dan kelas yang diampu oleh guru bersangkutan.
        Nilai Akhir dihitung dari rata-rata komponen penilaian (Tugas, Kuis, Ujian).
        Item Belum Dinilai: {{ ($hitungKosong ?? 'abaikan') === 'nol' ? 'Dihitung 0' : 'Diabaikan' }}.
        Status ketuntasan per mata pelajaran mengacu pada nilai KKM masing-masing mata pelajaran.
    </div>
</body>
</html>
