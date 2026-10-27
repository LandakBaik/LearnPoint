@extends('layouts.siswa')

@section('title', $quiz->judul . ' - Kuis Siswa LearnPoint')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Breadcrumb Navigation --}}
    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
        <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1">
            <i class="fa-solid fa-house text-[11px]"></i>
            <span>Beranda</span>
        </a>
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
        <a href="{{ route('siswa.kuis') }}" class="hover:text-blue-600 transition">
            Daftar Kuis
        </a>
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
        <span class="text-slate-700 truncate max-w-xs">{{ $quiz->judul }}</span>
    </div>

    @php
        $isCompleted = $nilai !== null;
        $isUpcoming = $quiz->waktu_mulai && now()->lessThan($quiz->waktu_mulai);
        $isExpired = $quiz->waktu_selesai && now()->greaterThan($quiz->waktu_selesai);
        $isAvailable = !$isCompleted && !$isUpcoming && !$isExpired;

        $mapelName = $quiz->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
        $guruName = $quiz->pengampuKelas?->guruMapel?->guru?->nama ?? 'Guru Pengampu';
        $kelasName = $quiz->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';
        $soalsCount = $quiz->soals_count ?? $quiz->soals->count();
    @endphp

    {{-- 1. Main Briefing Card --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
        {{-- Card Header Banner --}}
        <div class="relative bg-gradient-to-r from-blue-700 via-indigo-600 to-blue-800 p-6 md:p-8 text-white overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur-md text-white border border-white/25">
                            {{ $mapelName }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-blue-100">
                            Kelas {{ $kelasName }}
                        </span>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight leading-tight">
                        {{ $quiz->judul }}
                    </h1>

                    <div class="flex items-center gap-2 text-xs text-blue-100/90">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        <span>Disusun oleh: <strong>{{ $guruName }}</strong></span>
                    </div>
                </div>

                {{-- Status Badge Large --}}
                <div class="shrink-0">
                    @if($isCompleted)
                        <div class="px-5 py-3 rounded-2xl bg-emerald-500/20 backdrop-blur-md border border-emerald-300/40 text-center text-white">
                            <div class="text-xs uppercase font-bold text-emerald-200 tracking-wider">Hasil Kamu</div>
                            <div class="text-3xl font-black text-white mt-0.5">{{ number_format($nilai->nilai, 0) }}</div>
                            <div class="text-[10px] text-emerald-100 mt-0.5">Skor Akhir</div>
                        </div>
                    @elseif($isUpcoming)
                        <div class="px-4 py-2.5 rounded-2xl bg-amber-500/20 backdrop-blur-md border border-amber-300/40 text-center text-amber-200">
                            <i class="fa-solid fa-clock text-lg mb-1 block"></i>
                            <span class="text-xs font-bold">Belum Dimulai</span>
                        </div>
                    @elseif($isExpired)
                        <div class="px-4 py-2.5 rounded-2xl bg-rose-500/20 backdrop-blur-md border border-rose-300/40 text-center text-rose-200">
                            <i class="fa-solid fa-ban text-lg mb-1 block"></i>
                            <span class="text-xs font-bold">Sudah Berakhir</span>
                        </div>
                    @else
                        <div class="px-4 py-2.5 rounded-2xl bg-emerald-500/20 backdrop-blur-md border border-emerald-300/40 text-center text-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block mr-1 animate-ping"></span>
                            <span class="text-xs font-bold">Siap Dikerjakan</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Decorative graphics --}}
            <div class="absolute -right-6 -bottom-6 w-48 h-48 bg-white/5 rounded-full blur-xl pointer-events-none"></div>
        </div>

        {{-- Card Body --}}
        <div class="p-6 md:p-8 space-y-6">

            @if($quiz->deskripsi)
                <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100 text-xs text-slate-700 leading-relaxed">
                    <span class="font-bold text-blue-900 block mb-1">Catatan dari Guru:</span>
                    {{ $quiz->deskripsi }}
                </div>
            @endif

            {{-- Grid Informasi Detail --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-center">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-2 text-sm">
                        <i class="fa-solid fa-list-ol"></i>
                    </div>
                    <div class="text-xs text-slate-400 font-medium">Total Soal</div>
                    <div class="text-lg font-black text-slate-800 mt-0.5">{{ $soalsCount }} Butir</div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-center">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-2 text-sm">
                        <i class="fa-solid fa-fire"></i>
                    </div>
                    <div class="text-xs text-slate-400 font-medium">Arc Kesulitan</div>
                    <div class="text-sm font-bold text-slate-800 mt-1 capitalize truncate" title="{{ implode(', ', $quiz->kesulitan_aktif_array ?? []) }}">
                        {{ implode(', ', $quiz->kesulitan_aktif_array ?? ['Umum']) }}
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-center">
                    <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto mb-2 text-sm">
                        <i class="fa-solid fa-shuffle"></i>
                    </div>
                    <div class="text-xs text-slate-400 font-medium">Acak Jawaban</div>
                    <div class="text-sm font-bold {{ $quiz->acak_jawaban ? 'text-indigo-600' : 'text-slate-700' }} mt-1">
                        {{ $quiz->acak_jawaban ? 'Aktif' : 'Tidak Aktif' }}
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-center">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-sm">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="text-xs text-slate-400 font-medium">Status Pengerjaan</div>
                    <div class="text-sm font-bold {{ $isCompleted ? 'text-emerald-600' : 'text-blue-600' }} mt-1">
                        {{ $isCompleted ? 'Sudah Selesai' : 'Belum Selesai' }}
                    </div>
                </div>
            </div>

            {{-- Jadwal Kuis Alert --}}
            @if($quiz->waktu_mulai || $quiz->waktu_selesai)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <h4 class="text-xs font-bold text-slate-700 flex items-center gap-1.5 uppercase tracking-wider">
                        <i class="fa-regular fa-calendar-check text-blue-600"></i>
                        <span>Jadwal Pengerjaan Kuis</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-white border border-slate-200/70">
                            <span class="text-slate-400 block mb-0.5">Waktu Mulai:</span>
                            <span class="font-bold text-slate-700">
                                {{ $quiz->waktu_mulai ? $quiz->waktu_mulai->translatedFormat('l, d F Y, H:i') . ' WIB' : 'Kapan saja (Fleksibel)' }}
                            </span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200/70">
                            <span class="text-slate-400 block mb-0.5">Batas Pengumpulan (Selesai):</span>
                            <span class="font-bold {{ $isExpired ? 'text-rose-600' : 'text-slate-700' }}">
                                {{ $quiz->waktu_selesai ? $quiz->waktu_selesai->translatedFormat('l, d F Y, H:i') . ' WIB' : 'Tidak ada batas waktu' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Petunjuk Pengerjaan --}}
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-blue-600"></i>
                    <span>Petunjuk & Peraturan Pengerjaan:</span>
                </h3>
                <ul class="space-y-2 text-xs text-slate-600">
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 font-bold flex items-center justify-center shrink-0 text-[10px] mt-0.5">1</span>
                        <span>Pastikan koneksi internet kamu stabil sebelum menekan tombol mulai pengerjaan kuis.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 font-bold flex items-center justify-center shrink-0 text-[10px] mt-0.5">2</span>
                        <span>Bacalah setiap butir pertanyaan dengan teliti. Soal berbentuk pilihan ganda dengan 2 hingga 6 opsi pilihan jawaban.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 font-bold flex items-center justify-center shrink-0 text-[10px] mt-0.5">3</span>
                        <span>Waktu pengerjaan akan mulai dihitung begitu kamu menekan tombol <strong>"Mulai Kerjakan Kuis"</strong>.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 font-bold flex items-center justify-center shrink-0 text-[10px] mt-0.5">4</span>
                        <span>Setelah selesai menjawab semua pertanyaan, klik tombol <strong>"Kumpulkan Kuis"</strong> untuk mengakhiri dan melihat perolehan skormu secara langsung.</span>
                    </li>
                </ul>
            </div>

            {{-- 3. Action Buttons Section --}}
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <a 
                    href="{{ route('siswa.kuis') }}"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center justify-center gap-2 transition"
                >
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    <span>Kembali ke Daftar Kuis</span>
                </a>

                @if($isCompleted)
                    <a 
                        href="{{ route('siswa.kuis.hasil', $quiz->id) }}"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-md shadow-emerald-600/20"
                    >
                        <i class="fa-solid fa-award text-sm"></i>
                        <span>Lihat Rincian Hasil & Nilai</span>
                    </a>
                @elseif($isUpcoming)
                    <button 
                        type="button" 
                        disabled 
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center gap-2 cursor-not-allowed"
                    >
                        <i class="fa-solid fa-lock text-xs"></i>
                        <span>Kuis Belum Dimulai (Tunggu Waktu Buka)</span>
                    </button>
                @elseif($isExpired)
                    <button 
                        type="button" 
                        disabled 
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center gap-2 cursor-not-allowed"
                    >
                        <i class="fa-solid fa-ban text-xs"></i>
                        <span>Waktu Pengerjaan Telah Berakhir</span>
                    </button>
                @elseif($soalsCount == 0)
                    <button 
                        type="button" 
                        disabled 
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-amber-50 text-amber-700 font-bold text-xs flex items-center justify-center gap-2 cursor-not-allowed border border-amber-200"
                    >
                        <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                        <span>Belum Ada Soal dari Guru</span>
                    </button>
                @else
                    <button 
                        type="button" 
                        onclick="confirmStartQuiz()"
                        class="w-full sm:w-auto px-7 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-lg shadow-blue-600/30 group"
                    >
                        <span>Mulai Kerjakan Kuis Sekarang</span>
                        <i class="fa-solid fa-circle-play group-hover:scale-110 transition-transform"></i>
                    </button>
                @endif
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function confirmStartQuiz() {
    Swal.fire({
        title: 'Mulai Kuis Petualangan?',
        text: 'Waktu pengerjaan akan mulai dihitung setelah kamu masuk ke halaman soal. Pastikan kamu sudah siap!',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Mulai Sekarang!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ route('siswa.kuis.kerjakan', $quiz->id) }}";
        }
    });
}
</script>
@endpush
