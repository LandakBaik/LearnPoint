<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $mode === 'leger' ? 'Leger Nilai Mata Pelajaran' : 'Berita Acara Hasil Ujian' }}</title>
    <style>
        @page {
            margin: 14mm 12mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        .kop-surat {
            width: 100%;
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .kop-surat h1 {
            font-size: 15px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-surat h2 {
            font-size: 12.5px;
            margin: 4px 0 2px;
            font-weight: bold;
            color: #334155;
        }
        .kop-surat p {
            font-size: 10px;
            margin: 0;
            color: #64748b;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10.5px;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        table.data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.data-table th,
        table.data-table td {
            border: 1px solid #94a3b8;
            padding: 5px 4px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: middle;
        }
        table.data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            font-size: 9.5px;
            text-transform: uppercase;
            line-height: 1.25;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .merah { color: #dc2626; font-weight: bold; }
        .hijau { color: #059669; font-weight: bold; }
        .kuning { color: #b45309; font-style: italic; }
        .abu { color: #64748b; font-style: italic; }
        .tag-rs {
            font-size: 8.5px;
            font-weight: bold;
            color: #1d4ed8;
        }
        .legend-box {
            font-size: 9.5px;
            color: #475569;
            margin-top: 6px;
            margin-bottom: 22px;
        }
        .ttd-table {
            width: 100%;
            table-layout: fixed;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .ttd-space {
            height: 55px;
        }
    </style>
</head>
<body @if(!empty($autoPrint)) onload="window.print()" @endif>

    {{-- KOP SURAT SEKOLAH --}}
    <div class="kop-surat">
        <h1>SISTEM EVALUASI AKADEMIK CBT — LEARNPOINT</h1>
        <h2>
            {{ $mode === 'leger' ? 'LEGER REKAPITULASI NILAI MATA PELAJARAN' : 'BERITA ACARA & REKAPITULASI HASIL UJIAN' }}
        </h2>
        <p>
            Tahun Ajaran {{ $selectedTahunAjaran }} &bull; Semester {{ ucfirst($selectedSemester) }}
        </p>
    </div>

    {{-- METADATA KELAS & MAPEL --}}
    @php
        $namaGuru = auth()->user()->name ?? ($selectedPengampu?->guruMapel?->guru?->nama ?? 'Guru Mata Pelajaran');
    @endphp
    <table class="meta-table">
        <tr>
            <td style="width: 16%;"><strong>Mata Pelajaran</strong></td>
            <td style="width: 34%;">: {{ $selectedPengampu?->guruMapel?->mapel?->nama_mapel ?? '-' }}</td>
            <td style="width: 16%;"><strong>Tahun Ajaran</strong></td>
            <td style="width: 34%;">: {{ $selectedTahunAjaran }} ({{ ucfirst($selectedSemester) }})</td>
        </tr>
        <tr>
            <td><strong>Kelas</strong></td>
            <td>: {{ $selectedPengampu?->kelas?->nama_kelas ?? '-' }}</td>
            <td><strong>Guru Pengampu</strong></td>
            <td>: {{ $namaGuru }}</td>
        </tr>
        @if($mode === 'detail' && $selectedUjian)
            <tr>
                <td><strong>Nama Ujian</strong></td>
                <td>: {{ $selectedUjian->judul }}</td>
                <td><strong>KKM / Durasi</strong></td>
                <td>: {{ number_format((float)$selectedUjian->kkm, 0) }} / {{ $selectedUjian->durasi_menit }} Menit</td>
            </tr>
        @endif
    </table>

    {{-- TABEL 1: MODE LEGER SEMESTER --}}
    @if($mode === 'leger')
        @php
            $jmlUjian = max(1, $ujianRegulers->count());
            // Alokasi lebar: No (4%) + Nama (24%) + NISN (12%) + Rata-rata (10%) = 50%. Sisa 50% dibagi rata ke kolom ujian.
            $lebarPerUjian = round(50 / $jmlUjian, 2);
        @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 24%;">Nama Siswa</th>
                    <th style="width: 12%;">NISN</th>
                    @forelse($ujianRegulers as $u)
                        <th style="width: {{ $lebarPerUjian }}%;">
                            {{ $u->judul }}<br>
                            <span style="font-size: 8px; font-weight: normal;">(KKM: {{ number_format((float)$u->kkm, 0) }})</span>
                        </th>
                    @empty
                        <th style="width: 50%;">Belum Ada Ujian</th>
                    @endforelse
                    <th style="width: 10%;">Rata-rata</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barisLeger as $idx => $baris)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $baris['nama'] }}</td>
                        <td class="text-center">{{ $baris['nisn'] }}</td>

                        @forelse($ujianRegulers as $u)
                            @php $cell = $baris['nilai_per_ujian'][$u->id] ?? null; @endphp
                            <td class="text-center">
                                @if(!$cell || $cell['status'] === 'belum_ujian')
                                    <span class="abu">-</span>
                                @elseif($cell['status'] === 'menunggu_essay')
                                    <span class="kuning">Koreksi</span>
                                @else
                                    <span class="{{ $cell['tuntas'] ? '' : 'merah' }}">
                                        {{ number_format($cell['nilai'], 2) }}
                                    </span>
                                    @if($cell['kode'])
                                        <span class="tag-rs">({{ $cell['kode'] }})</span>
                                    @endif
                                @endif
                            </td>
                        @empty
                            <td class="text-center abu">-</td>
                        @endforelse

                        <td class="text-center font-bold">
                            {{ !is_null($baris['rata_rata']) ? number_format($baris['rata_rata'], 2) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 4 + max(1, $ujianRegulers->count()) }}" class="text-center">Tidak ada data siswa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    {{-- TABEL 2: MODE DETAIL SATU UJIAN (BERITA ACARA - TOTAL LEBAR PAS 100%) --}}
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 24%;">Nama Siswa</th>
                    <th style="width: 11%;">NISN</th>
                    <th style="width: 9%;">PG (B/S)</th>
                    <th style="width: 8%;">Skor PG</th>
                    <th style="width: 9%;">Skor Essay</th>
                    <th style="width: 9%;">Nilai Awal</th>
                    <th style="width: 11%;">Nilai Akhir</th>
                    <th style="width: 15%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barisDetail as $idx => $row)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $row['nama'] }}</td>
                        <td class="text-center">{{ $row['nisn'] }}</td>
                        <td class="text-center">
                            {{ $row['sudah_selesai'] && $row['total_pg'] > 0 ? ($row['pg_benar'] . ' / ' . $row['pg_salah']) : '-' }}
                        </td>
                        <td class="text-center">{{ $row['sudah_selesai'] ? $row['skor_pg'] : '-' }}</td>
                        <td class="text-center">
                            @if(!$row['sudah_selesai'] || $row['total_essay'] === 0)
                                -
                            @elseif($row['menunggu_essay'])
                                <span class="kuning">Koreksi</span>
                            @else
                                {{ $row['skor_essay'] }}
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $row['sudah_selesai'] && !$row['menunggu_essay'] ? number_format($row['nilai_awal'], 2) : '-' }}
                        </td>
                        <td class="text-center font-bold">
                            @if($row['sudah_selesai'] && !$row['menunggu_essay'])
                                <span class="{{ $row['status_kategori'] === 'tuntas' ? '' : 'merah' }}">
                                    {{ number_format($row['nilai_akhir'], 2) }}
                                </span>
                                @if($row['kode'])
                                    <span class="tag-rs">({{ $row['kode'] }})</span>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="{{ $row['status_kategori'] === 'tuntas' ? 'hijau' : ($row['status_kategori'] === 'remidi' ? 'merah' : ($row['status_kategori'] === 'menunggu_essay' ? 'kuning' : 'abu')) }}">
                                {{ $row['status_label'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data peserta.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- KETERANGAN SIMBOL --}}
    <div class="legend-box">
        <strong>Keterangan Simbol:</strong>
        <strong>(R)</strong> = Nilai akhir hasil pelaksanaan Ujian Remidi &bull;
        <strong>(S)</strong> = Nilai hasil pelaksanaan Ujian Susulan &bull;
        <strong>(-)</strong> = Siswa belum mengikuti ujian.
    </div>

    {{-- BLOK TANDA TANGAN --}}
    <table class="ttd-table">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah
                <div class="ttd-space"></div>
                <strong>( ................................................ )</strong><br>
                NIP. ........................................
            </td>
            <td>
                Dicetak pada: {{ now()->translatedFormat('d F Y') }}<br>
                Guru Mata Pelajaran
                <div class="ttd-space"></div>
                <strong>{{ $namaGuru }}</strong><br>
                NIP. ........................................
            </td>
        </tr>
    </table>

</body>
</html>
