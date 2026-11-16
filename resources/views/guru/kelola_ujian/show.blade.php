@extends('layouts.app')

@section('title', 'Detail Ujian')

@section('content')

@php
    $anggotaGrup = isset($grupUjians) && $grupUjians->isNotEmpty()
        ? $grupUjians
        : collect([$ujian]);

    $daftarKelas = $anggotaGrup
        ->map(fn($item) => $item->pengampuKelas?->kelas?->nama_kelas ?? $item->kelas?->nama_kelas)
        ->filter()
        ->unique()
        ->values();

    $adaPengerjaan = $anggotaGrup->sum(fn($item) => $item->seleksiUjians->count()) > 0;

    // Tombol Edit hanya muncul jika status BUKAN published dan BUKAN completed (serta belum ada pengerjaan)
    // Tombol Edit tampil untuk draft, scheduled (0 peserta), unpublished (Edit Bobot), dan completed (Edit Bobot)
    $bisaEdit = $ujian->bolehDiedit();
    $labelTombolEdit = $ujian->hanyaBisaEditBobot() ? 'Edit Bobot Soal' : 'Edit Ujian';
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
            <div class="flex items-center gap-3 mb-2">
                <a
                    href="{{ route('guru.ujian.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-blue-600 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Daftar Ujian
                </a>
            </div>

            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">
                {{ $ujian->judul }}
            </h1>

            <p class="text-gray-500 mt-1 text-sm">
                Detail pengaturan instrumen ujian dan butir evaluasi.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">

            {{-- Hapus tombol Edit jika status adalah published (Publikasi) atau completed (Selesai) --}}
            @if($bisaEdit)
                <a
                    href="{{ route('guru.ujian.edit', $ujian) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>{{ $labelTombolEdit }}</span>
                </a>
            @endif

            @if($ujian->status === 'draft')

                <form
                    action="{{ route('guru.ujian.publish', $ujian) }}"
                    method="POST"
                    onsubmit="return confirm('Publikasikan ujian ini sekarang untuk semua kelas target?')"
                >
                    @csrf
                    <input type="hidden" name="tipe_publikasi" value="langsung">

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition shadow-sm cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Publikasikan</span>
                    </button>
                </form>

            @endif

        </div>
    </div>


    {{-- INFORMASI UTAMA --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- STATUS --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
            <p class="text-sm text-gray-500 mb-2">Status</p>
            <span
                class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                @if($ujian->status === 'draft')
                    bg-gray-100 text-gray-700 border border-gray-200
                @elseif($ujian->status === 'scheduled')
                    bg-amber-50 text-amber-700 border border-amber-200
                @elseif($ujian->status === 'published')
                    bg-emerald-50 text-emerald-700 border border-emerald-200
                @elseif($ujian->status === 'completed')
                    bg-slate-100 text-slate-700 border border-slate-200
                @else
                    bg-rose-50 text-rose-700 border border-rose-200
                @endif"
            >
                {{ $ujian->status_label ?? ucfirst($ujian->status) }}
            </span>
        </div>

        {{-- MATA PELAJARAN --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
            <p class="text-sm text-gray-500 mb-2">Mata Pelajaran</p>
            <p class="font-bold text-gray-800">
                {{ $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? $ujian->mapel?->nama_mapel ?? '-' }}
            </p>
        </div>

        {{-- KELAS TARGET (MENAMPILKAN SEMUA KELAS DALAM GRUP UJIAN) --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Target Kelas</p>
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                    {{ $daftarKelas->count() }} Kelas
                </span>
            </div>
            @if($daftarKelas->isNotEmpty())
                <div class="flex flex-wrap gap-1.5">
                    @foreach($daftarKelas as $namaKls)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-gray-800 border border-slate-200">
                            {{ $namaKls }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="font-bold text-gray-800">-</p>
            @endif
        </div>

        {{-- JUMLAH SOAL --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
            <p class="text-sm text-gray-500 mb-2">Jumlah Soal</p>
            <p class="text-2xl font-black text-gray-800">
                {{ $ujian->soals->count() }}
            </p>
        </div>

    </div>


    {{-- DETAIL PENGATURAN --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- INFORMASI UJIAN --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-xs border border-gray-100">

            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Informasi Ujian</h2>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <p class="text-sm text-gray-500">Durasi</p>
                    <p class="font-semibold text-gray-800 mt-1">{{ $ujian->durasi_menit }} menit</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">KKM</p>
                    <p class="font-semibold text-gray-800 mt-1">{{ $ujian->kkm }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Waktu Mulai</p>
                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $ujian->waktu_mulai ? \Carbon\Carbon::parse($ujian->waktu_mulai)->isoFormat('D MMMM Y, HH:mm') . ' WIB' : 'Belum dijadwalkan' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Deadline</p>
                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $ujian->deadline ? \Carbon\Carbon::parse($ujian->deadline)->isoFormat('D MMMM Y, HH:mm') . ' WIB' : 'Belum ditentukan' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Dibuat</p>
                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $ujian->created_at ? $ujian->created_at->isoFormat('D MMM Y, HH:mm') : '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Dipublikasi</p>
                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $ujian->dipublikasi_at ? \Carbon\Carbon::parse($ujian->dipublikasi_at)->isoFormat('D MMM Y, HH:mm') : '-' }}
                    </p>
                </div>

            </div>

        </div>


        {{-- RINGKASAN SOAL --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100">

            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Komposisi Soal</h2>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Pilihan Ganda Biasa</span>
                    <span class="font-bold text-gray-800">{{ $ujian->soals->where('tipe_soal', 'single_choice')->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Pilihan Ganda Kompleks</span>
                    <span class="font-bold text-gray-800">{{ $ujian->soals->where('tipe_soal', 'multiple_choice')->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Essay</span>
                    <span class="font-bold text-gray-800">{{ $ujian->soals->where('tipe_soal', 'essay')->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Menjodohkan</span>
                    <span class="font-bold text-gray-800">{{ $ujian->soals->where('tipe_soal', 'matching')->count() }}</span>
                </div>

                <div class="border-t border-gray-100 pt-4 flex justify-between items-center">
                    <span class="font-bold text-gray-700">Total Bobot</span>
                    <span class="font-black text-blue-600 text-lg">
                        {{ round($ujian->soals->sum('bobot'), 2) }} / 100
                    </span>
                </div>
            </div>

        </div>

    </div>


    {{-- KISI-KISI --}}
    @if($ujian->kisi_kisi)
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Kisi-kisi Ujian</h2>
            </div>
            <div class="p-6">
                <p class="text-gray-700 whitespace-pre-line leading-relaxed text-sm">
                    {{ $ujian->kisi_kisi }}
                </p>
            </div>
        </div>
    @endif


    {{-- DAFTAR SOAL --}}
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100">

        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Daftar Butir Soal</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Periksa kembali butir soal dan kebenaran kunci jawaban sebelum ujian diikuti siswa.
                </p>
            </div>
            <span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1 rounded-full">
                {{ $ujian->soals->count() }} Butir Soal
            </span>
        </div>

        <div class="p-6 space-y-6">

            @forelse($ujian->soals as $index => $soal)
                @php
                    $rawKunci = $soal->kunci_jawaban;
                    if (is_string($rawKunci)) {
                        $decoded = json_decode($rawKunci, true);
                        $kunciArr = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                            ? $decoded
                            : array_filter(explode(',', $rawKunci));
                    } elseif (is_array($rawKunci)) {
                        $kunciArr = $rawKunci;
                    } else {
                        $kunciArr = $rawKunci !== null ? [$rawKunci] : [];
                    }

                    $kunciNormalized = array_map(function($k) {
                        return strtolower(trim((string)$k));
                    }, $kunciArr);

                    $labelKunci = [];
                    if (is_array($soal->pilihan)) {
                        foreach ($soal->pilihan as $keyOpt => $textOpt) {
                            $hKecil = chr(97 + (int)$keyOpt);
                            $hBesar = chr(65 + (int)$keyOpt);
                            $idxStr = (string)$keyOpt;

                            if (in_array($hKecil, $kunciNormalized) || in_array($idxStr, $kunciNormalized)) {
                                $labelKunci[] = $hBesar . '. ' . $textOpt;
                            }
                        }
                    }
                @endphp

                <div class="border border-gray-200 rounded-2xl p-5 bg-white space-y-4 hover:border-gray-300 transition">

                    {{-- HEADER SOAL --}}
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-black text-sm border border-blue-100">
                                {{ $index + 1 }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-600">
                                @switch($soal->tipe_soal)
                                    @case('single_choice') Pilihan Tunggal @break
                                    @case('multiple_choice') Pilihan Ganda Kompleks @break
                                    @case('essay') Essay @break
                                    @case('matching') Menjodohkan @break
                                    @default {{ $soal->tipe_soal }}
                                @endswitch
                            </span>
                        </div>

                        <div class="text-xs font-semibold text-gray-500">
                            Bobot: <span class="font-bold text-gray-800">{{ $soal->bobot }} Poin</span>
                        </div>
                    </div>

                    {{-- TEKS PERTANYAAN --}}
                    <div class="text-gray-900 font-medium leading-relaxed text-sm">
                        {!! nl2br(e($soal->pertanyaan)) !!}
                    </div>

                    {{-- GAMBAR SOAL --}}
                    @if($soal->gambar)
                        <div class="my-3">
                            <div class="inline-block p-1.5 border border-gray-200 rounded-2xl bg-gray-50 shadow-2xs">
                                <img
                                    src="{{ asset('storage/' . ltrim($soal->gambar, '/')) }}"
                                    alt="Gambar soal {{ $index + 1 }}"
                                    class="max-w-md max-h-72 w-auto rounded-xl object-contain block bg-white"
                                    onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'p-3 text-xs text-rose-600 font-semibold flex items-center gap-1.5\'>⚠️ Gambar belum terhubung. Jalankan: <code>php artisan storage:link</code> di terminal.</div>';"
                                >
                            </div>
                        </div>
                    @endif

                    {{-- OPSI JAWABAN (PILIHAN GANDA) --}}
                    @if(is_array($soal->pilihan) && count($soal->pilihan))
                        <div class="space-y-2 pt-1">
                            @foreach($soal->pilihan as $key => $pilihan)
                                @php
                                    $hurufKecil = chr(97 + (int)$key);
                                    $hurufBesar = chr(65 + (int)$key);
                                    $indexStr   = (string)$key;

                                    $isBenar = in_array($hurufKecil, $kunciNormalized)
                                            || in_array($indexStr, $kunciNormalized);
                                @endphp

                                <div
                                    class="flex items-center justify-between p-3 rounded-xl border text-sm transition
                                    {{ $isBenar
                                        ? 'bg-emerald-50/80 border-emerald-300 ring-1 ring-emerald-300/40 text-emerald-950 font-semibold'
                                        : 'bg-gray-50/60 border-gray-200/80 text-gray-700 font-normal' }}"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-7 h-7 rounded-lg text-xs font-bold flex items-center justify-center shrink-0
                                            {{ $isBenar ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-200 text-gray-600' }}">
                                            {{ $hurufBesar }}
                                        </span>
                                        <span class="truncate">{{ $pilihan }}</span>
                                    </div>

                                    @if($isBenar)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0 ml-2">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Kunci Jawaban
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- RINGKASAN KUNCI JAWABAN SOAL INI --}}
                        <div class="pt-2 flex items-center gap-2 text-xs">
                            <span class="font-bold text-gray-500 uppercase tracking-wider">Kunci Benar:</span>
                            @if(count($labelKunci) > 0)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($labelKunci as $itemKunci)
                                        <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                                            {{ $itemKunci }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-rose-500 italic font-semibold">Belum disetel</span>
                            @endif
                        </div>

                    @elseif($soal->tipe_soal === 'essay')
                        <div class="p-4 bg-blue-50/60 border border-blue-200 rounded-xl space-y-1">
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-700 block">Kunci / Pedoman Jawaban Essay:</span>
                            <p class="text-sm text-gray-800 font-medium">
                                {{ !empty($kunciNormalized) ? implode(', ', $kunciNormalized) : 'Guru akan mengoreksi jawaban essay siswa secara manual.' }}
                            </p>
                        </div>

                    @elseif($soal->tipe_soal === 'matching')
                        <div class="p-4 bg-purple-50/60 border border-purple-200 rounded-xl text-xs text-purple-700 font-medium">
                            Soal tipe menjodohkan (pasangan pilihan telah disetel di instrumen soal).
                        </div>
                    @endif

                </div>

            @empty
                <div class="text-center py-12">
                    <div class="text-4xl mb-3">📝</div>
                    <h3 class="font-bold text-gray-700">Belum ada soal</h3>
                    <p class="text-sm text-gray-500 mt-1">Tambahkan butir soal terlebih dahulu melalui menu edit.</p>
                </div>
            @endforelse

        </div>

    </div>

    {{-- PERINGATAN --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex gap-3">
            <div class="text-xl">⚠️</div>
            <div>
                <h3 class="font-bold text-amber-800 text-sm">Pemeriksaan Kunci Jawaban</h3>
                <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                    Pastikan seluruh butir soal telah memiliki lencana hijau <strong>Kunci Jawaban</strong> sebelum mempublikasikan ujian agar sistem dapat melakukan penilaian otomatis dengan tepat.
                </p>
            </div>
        </div>
    </div>

</div>

@endsection
