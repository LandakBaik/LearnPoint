@extends('layouts.app')

@section('title', 'Laporan & Leger Nilai Ujian')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('guru.ujian.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-blue-600 transition">
                &larr; Kembali ke Pusat Ujian
            </a>
            <h1 class="text-2xl font-black text-gray-900 mt-1">
                 Laporan & Leger Nilai Ujian
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Rekapitulasi nilai akhir ujian reguler, remidi (R), dan susulan (S) berdasarkan Tahun Ajaran & Semester.
            </p>
        </div>

        {{-- Tombol Export PDF --}}
        @if($selectedPengampu)
            <a href="{{ route('guru.ujian.laporan.pdf', request()->query()) }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold transition shadow-sm shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export & Download PDF ({{ $mode === 'leger' ? 'Leger Semester' : 'Berita Acara Ujian' }})</span>
            </a>
        @endif
    </div>

    {{-- FORM FILTER TAHAP 1 --}}
    <div class="bg-white rounded-3xl border border-gray-200 p-5 sm:p-6 shadow-xs">
        <form method="GET" action="{{ route('guru.ujian.laporan') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">

            {{-- 1. Tahun Ajaran --}}
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1.5">
                    1. Tahun Ajaran
                </label>
                <select name="tahun_ajaran" onchange="this.form.submit()"
                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-xs font-bold text-gray-800 focus:border-blue-500 outline-none cursor-pointer">
                    @foreach($daftarTahunAjaran as $ta)
                        <option value="{{ $ta }}" {{ $selectedTahunAjaran === $ta ? 'selected' : '' }}>
                            {{ $ta }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 2. Semester --}}
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1.5">
                    2. Semester
                </label>
                <select name="semester" onchange="this.form.submit()"
                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-xs font-bold text-gray-800 focus:border-blue-500 outline-none cursor-pointer">
                    <option value="ganjil" {{ $selectedSemester === 'ganjil' ? 'selected' : '' }}>
                        Ganjil (Juli – Desember)
                    </option>
                    <option value="genap" {{ $selectedSemester === 'genap' ? 'selected' : '' }}>
                        Genap (Januari – Juni)
                    </option>
                </select>
            </div>

            {{-- 3. Mata Pelajaran & Kelas (PengampuKelas) --}}
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1.5">
                    3. Mapel & Kelas
                </label>
                <select name="pengampu_kelas_id" onchange="this.form.submit()"
                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-xs font-bold text-gray-800 focus:border-blue-500 outline-none cursor-pointer">
                    @foreach($pengampuList as $item)
                        <option value="{{ $item->id }}" {{ $selectedPengampu?->id === $item->id ? 'selected' : '' }}>
                            {{ $item->guruMapel->mapel->nama_mapel }} — Kelas {{ $item->kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 4. Mode Laporan --}}
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1.5">
                    4. Jenis Laporan PDF
                </label>
                <select name="mode" onchange="this.form.submit()"
                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-xs font-bold text-indigo-700 bg-indigo-50/50 focus:border-indigo-500 outline-none cursor-pointer">
                    <option value="leger" {{ $mode === 'leger' ? 'selected' : '' }}>
                        📑 Leger Mapel (Multi-Ujian)
                    </option>
                    <option value="detail" {{ $mode === 'detail' ? 'selected' : '' }}>
                        📋 Detail Satu Ujian (Berita Acara)
                    </option>
                </select>
            </div>

            {{-- 5. Pilih Ujian Spesifik (Jika Mode = Detail) atau Tombol Tampilkan --}}
            @if($mode === 'detail')
                <div>
                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1.5">
                        5. Pilih Ujian
                    </label>
                    <select name="ujian_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-xs font-bold text-gray-800 focus:border-blue-500 outline-none cursor-pointer">
                        @forelse($ujianRegulers as $u)
                            <option value="{{ $u->id }}" {{ $selectedUjian?->id === $u->id ? 'selected' : '' }}>
                                {{ $u->judul }} ({{ $u->waktu_mulai ? $u->waktu_mulai->format('d/m/Y') : 'Tanpa Tanggal' }})
                            </option>
                        @empty
                            <option value="">-- Tidak ada ujian --</option>
                        @endforelse
                    </select>
                </div>
            @else
                <div>
                    <button type="submit"
                        class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold transition cursor-pointer">
                        Terapkan Filter
                    </button>
                </div>
            @endif

        </form>
    </div>

    {{-- QUALITY GATE WARNING BANNER
    @if($qualityGate['menunggu_essay'] > 0 || $qualityGate['belum_ujian'] > 0)
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="text-xl">⚠️</span>
                <div class="text-xs text-amber-900">
                    <p class="font-extrabold">Perhatian Sebelum Mencetak PDF Resmi:</p>
                    <p class="mt-0.5">
                        Masih terdapat
                        @if($qualityGate['menunggu_essay'] > 0)
                            <strong class="underline">{{ $qualityGate['menunggu_essay'] }} pengerjaan ujian yang menunggu koreksi essay</strong>
                        @endif
                        @if($qualityGate['menunggu_essay'] > 0 && $qualityGate['belum_ujian'] > 0)
                            dan
                        @endif
                        @if($qualityGate['belum_ujian'] > 0)
                            <strong>{{ $qualityGate['belum_ujian'] }} slot ujian yang belum dikerjakan siswa</strong>.
                        @endif
                        Pastikan seluruh koreksi essay atau ujian susulan telah selesai jika ingin mencetak nilai rapor akhir.
                    </p>
                </div>
            </div>
        </div>
    @endif --}}

    {{-- =========================================================
        PREVIEW TABEL MODE 1: LEGER MAPEL (MULTI-UJIAN)
    ========================================================== --}}
    @if($mode === 'leger')
        <div class="bg-white rounded-3xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-black text-gray-900">
                        Preview Leger Nilai: {{ $selectedPengampu?->guruMapel?->mapel?->nama_mapel ?? '-' }}
                        (Kelas {{ $selectedPengampu?->kelas?->nama_kelas ?? '-' }})
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Tahun Ajaran {{ $selectedTahunAjaran }} &bull; Semester {{ ucfirst($selectedSemester) }}
                        ({{ $rentangMulai->translatedFormat('d M Y') }} – {{ $rentangSelesai->translatedFormat('d M Y') }})
                    </p>
                </div>

                <div class="flex items-center gap-3 text-[11px] font-bold">
                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200">
                        (R) = Nilai Hasil Remidi
                    </span>
                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-200">
                        (S) = Nilai Ujian Susulan
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-extrabold text-gray-500 uppercase">
                            <th class="py-3.5 px-4 w-10 text-center">No</th>
                            <th class="py-3.5 px-4 min-w-[200px]">Nama Siswa</th>
                            <th class="py-3.5 px-4 w-28">NISN</th>
                            @forelse($ujianRegulers as $idxUjian => $u)
                                <th class="py-3.5 px-4 text-center min-w-[120px] border-l border-gray-100">
                                    <div class="text-gray-800 font-black truncate max-w-[140px] mx-auto" title="{{ $u->judul }}">
                                        {{ $u->judul }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-semibold mt-0.5">
                                        KKM: {{ number_format((float)$u->kkm, 0) }}
                                    </div>
                                </th>
                            @empty
                                <th class="py-3.5 px-4 text-center border-l border-gray-100 text-gray-400 italic">
                                    Belum ada ujian pada semester ini
                                </th>
                            @endforelse
                            <th class="py-3.5 px-4 text-center w-28 border-l border-gray-200 bg-indigo-50/50 text-indigo-900">
                                Rata-rata
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($barisLeger as $idx => $baris)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-center font-bold text-gray-400">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 font-bold text-gray-900">{{ $baris['nama'] }}</td>
                                <td class="py-3 px-4 text-gray-500 font-mono">{{ $baris['nisn'] }}</td>

                                @forelse($ujianRegulers as $u)
                                    @php $cell = $baris['nilai_per_ujian'][$u->id] ?? null; @endphp
                                    <td class="py-3 px-4 text-center border-l border-gray-100">
                                        @if(!$cell || $cell['status'] === 'belum_ujian')
                                            <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-500 text-[10px] font-bold">
                                                Belum Ujian
                                            </span>
                                        @elseif($cell['status'] === 'menunggu_essay')
                                            <a href="{{ route('guru.ujian.koreksi-essay', $u->id) }}"
                                                class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-extrabold hover:underline">
                                                ⏳ Koreksi Essay
                                            </a>
                                        @else
                                            <span class="font-black text-sm {{ $cell['tuntas'] ? 'text-gray-900' : 'text-rose-600' }}">
                                                {{ number_format($cell['nilai'], 2) }}
                                            </span>
                                            @if($cell['kode'] === 'R')
                                                <span class="ml-1 px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800"
                                                    title="Tuntas lewat Remidi (Nilai awal: {{ $cell['nilai_awal'] }})">
                                                    (R)
                                                </span>
                                            @elseif($cell['kode'] === 'S')
                                                <span class="ml-1 px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-blue-100 text-blue-800"
                                                    title="Nilai dari Ujian Susulan">
                                                    (S)
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                @empty
                                    <td class="py-3 px-4 text-center text-gray-300 border-l border-gray-100">-</td>
                                @endforelse

                                <td class="py-3 px-4 text-center border-l border-gray-200 bg-indigo-50/30 font-black text-sm text-indigo-700">
                                    {{ !is_null($baris['rata_rata']) ? number_format($baris['rata_rata'], 2) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + max(1, $ujianRegulers->count()) }}" class="py-10 text-center text-gray-400">
                                    Tidak ditemukan data anggota kelas untuk Tahun Ajaran {{ $selectedTahunAjaran }} Semester {{ ucfirst($selectedSemester) }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- =========================================================
        PREVIEW TABEL MODE 2: DETAIL SATU UJIAN (BERITA ACARA)
    ========================================================== --}}
    @else
        <div class="bg-white rounded-3xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-black text-gray-900">
                        Preview Berita Acara Ujian: {{ $selectedUjian?->judul ?? 'Belum Dipilih' }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Kelas {{ $selectedPengampu?->kelas?->nama_kelas ?? '-' }} &bull;
                        KKM: <strong>{{ number_format((float)($selectedUjian?->kkm ?? 75), 0) }}</strong> &bull;
                        Tanggal: {{ $selectedUjian?->waktu_mulai ? $selectedUjian->waktu_mulai->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-extrabold text-gray-500 uppercase">
                            <th class="py-3.5 px-4 w-10 text-center">No</th>
                            <th class="py-3.5 px-4">Nama Siswa</th>
                            <th class="py-3.5 px-4">NISN</th>
                            <th class="py-3.5 px-4 text-center">Benar / Salah PG</th>
                            <th class="py-3.5 px-4 text-center">Skor PG</th>
                            <th class="py-3.5 px-4 text-center">Skor Essay</th>
                            <th class="py-3.5 px-4 text-center">Nilai Awal</th>
                            <th class="py-3.5 px-4 text-center">Nilai Akhir</th>
                            <th class="py-3.5 px-4 text-center">Status KKM</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($barisDetail as $idx => $row)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-center font-bold text-gray-400">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 font-bold text-gray-900">{{ $row['nama'] }}</td>
                                <td class="py-3 px-4 text-gray-500 font-mono">{{ $row['nisn'] }}</td>
                                <td class="py-3 px-4 text-center">
                                    @if($row['sudah_selesai'] && $row['total_pg'] > 0)
                                        <span class="text-emerald-600 font-bold">{{ $row['pg_benar'] }}B</span> /
                                        <span class="text-rose-600 font-bold">{{ $row['pg_salah'] }}S</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-semibold">
                                    {{ $row['sudah_selesai'] ? $row['skor_pg'] : '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-semibold">
                                    @if(!$row['sudah_selesai'] || $row['total_essay'] === 0)
                                        -
                                    @elseif($row['menunggu_essay'])
                                        <span class="text-amber-600 font-bold">Belum Dikoreksi</span>
                                    @else
                                        {{ $row['skor_essay'] }}
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center text-gray-600">
                                    {{ $row['sudah_selesai'] && !$row['menunggu_essay'] ? number_format($row['nilai_awal'], 2) : '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-black text-sm">
                                    @if($row['sudah_selesai'] && !$row['menunggu_essay'])
                                        {{ number_format($row['nilai_akhir'], 2) }}
                                        @if($row['kode'])
                                            <span class="text-[10px] font-extrabold text-indigo-600">({{ $row['kode'] }})</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                        {{ $row['status_kategori'] === 'tuntas' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                        {{ $row['status_kategori'] === 'remidi' ? 'bg-rose-50 text-rose-700' : '' }}
                                        {{ $row['status_kategori'] === 'menunggu_essay' ? 'bg-amber-50 text-amber-800' : '' }}
                                        {{ $row['status_kategori'] === 'belum_ujian' ? 'bg-gray-100 text-gray-600' : '' }}">
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-10 text-center text-gray-400">
                                    Belum ada data ujian atau siswa untuk ditampilkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
