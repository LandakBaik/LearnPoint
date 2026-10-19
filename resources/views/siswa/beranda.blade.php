@extends('layouts.siswa')

@section('title', 'Beranda Siswa - LearnPoint')

@section('content')
<div class="space-y-5">

    {{-- Header Mata Pelajaran --}}
    <div class="flex items-center gap-3">
        <h2 class="text-lg font-extrabold text-slate-800 tracking-tight">
            Daftar Mata Pelajaran
        </h2>

        <span class="px-2.5 py-1 bg-blue-50 text-blue-600 font-bold text-xs rounded-full border border-blue-100">
            {{ $daftarMapel->count() }} Pelajaran
        </span>
    </div>

    {{-- Grid Mata Pelajaran --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        @forelse ($daftarMapel as $mapel)

            @php
                $guruMapelFirst = $mapel->guruMapels?->first();
                $guruModel = $guruMapelFirst?->guru;
                $namaGuru = $guruModel->nama ?? $guruModel->name ?? 'Guru Pengampu';

                $jumlahBab = $mapel->babs_count ?? (isset($mapel->babs) ? $mapel->babs->count() : 0);
                $babAktifUrutan = $jumlahBab > 0 ? 1 : 0;
                $progressPersen = $jumlahBab > 0 ? round(($babAktifUrutan / $jumlahBab) * 100) : 0;
            @endphp

            {{-- Card Mata Pelajaran --}}
            <a
                href="{{ route('siswa.materi.show', $mapel->id) }}"
                class="group relative block overflow-hidden bg-white rounded-2xl border border-blue-100/80 shadow-[0_6px_20px_rgba(59,130,246,0.07)] hover:shadow-[0_10px_25px_rgba(59,130,246,0.14)] hover:-translate-y-1 transition-all duration-300 p-4 flex flex-col justify-between h-full space-y-4">

                {{-- Dekorasi Card --}}
                <div class="absolute -top-5 -left-5 w-16 h-12 bg-blue-100/80 rounded-br-[28px] rounded-tl-2xl -rotate-6 pointer-events-none"></div>
                <div class="absolute top-24 -right-7 w-20 h-20 bg-blue-50 rounded-full pointer-events-none"></div>
                <div class="absolute -bottom-5 -right-3 w-16 h-10 bg-gradient-to-br from-blue-100 to-blue-300/80 rounded-tl-[30px] rotate-[-12deg] pointer-events-none"></div>

                {{-- Informasi Mata Pelajaran --}}
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">

                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-100 text-blue-600 flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform duration-300">
                            <i class="fa-solid fa-book text-base"></i>
                        </div>

                        <span class="px-3 py-1.5 bg-blue-50 text-blue-600 font-bold text-[11px] rounded-full border border-blue-100/70">
                            Kelas {{ $siswa->kelas->nama_kelas ?? '9' }}
                        </span>

                    </div>

                    <h3 class="text-base font-extrabold text-slate-800 leading-snug group-hover:text-blue-600 transition-colors mb-1">
                        {{ $mapel->nama_mapel }}
                    </h3>

                    <div class="flex items-center gap-1.5 text-xs font-medium text-slate-400">
                        <i class="fa-regular fa-user text-blue-400 text-[11px]"></i>
                        <span>{{ $namaGuru }}</span>
                    </div>
                </div>

                {{-- Progress Bab --}}
                <div class="relative z-10 pt-3 border-t border-blue-100/70">

                    <div class="flex items-center justify-between text-[11px] font-bold mb-2">
                        <span class="text-slate-400">
                            Progress: Bab {{ $babAktifUrutan }} / {{ $jumlahBab }}
                        </span>

                        <span class="text-blue-600">
                            {{ $progressPersen }}%
                        </span>
                    </div>

                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div
                            class="bg-gradient-to-r from-blue-400 to-blue-600 h-full rounded-full transition-all duration-500"
                            style="--progress: {{ $progressPersen ?? 0 }}%; width: var(--progress);">
                        </div>
                    </div>

                </div>
            </a>

        @empty

            {{-- Kondisi Data Kosong --}}
            <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-dashed border-blue-200 text-slate-400">
                <i class="fa-solid fa-book-open text-2xl text-blue-300 mb-2"></i>

                <p class="text-xs font-semibold">
                    Belum ada data mata pelajaran yang tersedia.
                </p>
            </div>

        @endforelse

    </div>
</div>
@endsection