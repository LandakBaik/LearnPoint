@extends('layouts.siswa')

@section('title', 'Konfirmasi Token Ujian - ' . $ujian->judul)

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">

        {{-- Header Info Ujian & Sesi --}}
        <div class="text-center space-y-2 pb-6 border-b border-slate-100">
            <div class="inline-flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">
                    {{ $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran' }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    SESI {{ $ujian->sesi ?? 1 }}
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-1">{{ $ujian->judul }}</h1>
            <p class="text-xs text-slate-500">
                Kelas: <strong class="text-slate-800">{{ $ujian->pengampuKelas?->kelas?->nama_kelas ?? '-' }}</strong> &bull;
                Peserta: <strong class="text-slate-800">{{ auth()->user()->name }}</strong>
            </p>
        </div>

        {{-- Kartu Ringkasan Jadwal Sesi --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-center">
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jadwal Sesi {{ $ujian->sesi ?? 1 }}</p>
                <p class="text-sm font-black text-indigo-600 mt-1">
                    {{ $waktuMulaiSesi->format('H:i') }} – {{ $batasAkhirSesi->format('H:i') }} WIB
                </p>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Durasi Maksimal</p>
                <p class="text-sm font-black text-slate-800 mt-1">{{ $ujian->durasi_menit }} Menit</p>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jumlah Soal</p>
                <p class="text-sm font-black text-slate-800 mt-1">{{ $ujian->soals()->count() }} Butir</p>
            </div>
        </div>

        {{-- Ketentuan Pelaksanaan --}}
        <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 space-y-2 text-xs text-amber-900">
            <div class="font-extrabold flex items-center gap-1.5 text-amber-950">
                <span>⚠️</span> Ketentuan Sesi & Anti-Curang:
            </div>
            <ul class="list-disc list-inside space-y-1 text-[11px] leading-relaxed text-amber-900/90 pl-1">
                <li><strong>Waktu mengikuti jadwal sesi:</strong> Hitung mundur berjalan sejak pukul <strong>{{ $waktuMulaiSesi->format('H:i') }} WIB</strong>. Siswa yang terlambat masuk hanya mendapatkan sisa waktu sesi yang tersisa.</li>
                <li>Wajib mengaktifkan <strong>Mode Layar Penuh (Fullscreen)</strong> selama mengerjakan.</li>
                <li>Keluar dari layar penuh, berpindah tab, atau membuka aplikasi lain sebanyak 3 kali akan <strong>mengunci sesi ujian secara otomatis</strong>.</li>
            </ul>
        </div>

        {{-- Form Input Token --}}
        <form action="{{ route('siswa.ujian.validasi-token', $ujian->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="token" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2 text-center">
                    Masukkan Token Sesi {{ $ujian->sesi ?? 1 }}
                </label>
                <div class="max-w-xs mx-auto">
                    <input type="text" name="token" id="token" required maxlength="10" autocomplete="off"
                        placeholder="Contoh: S1-7B9X"
                        value="{{ old('token') }}"
                        class="w-full text-center text-2xl font-black font-mono tracking-widest uppercase py-3.5 px-4 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 outline-none transition placeholder:text-slate-300 placeholder:font-sans placeholder:text-sm placeholder:tracking-normal">
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
                <a href="{{ route('siswa.dashboard') }}"
                    class="w-1/3 py-3 text-center text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                    &larr; Kembali
                </a>
                <button type="submit"
                    class="w-2/3 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-extrabold transition shadow-md cursor-pointer flex items-center justify-center gap-2">
                    <span>Verifikasi Token & Mulai</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
