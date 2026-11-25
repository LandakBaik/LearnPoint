@extends('layouts.app')

@section('title', 'Lembar Jawaban: ' . ($siswa->user->name ?? 'Siswa'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="detailJawabanApp()">

    {{-- =========================================================
        HEADER HALAMAN
    ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div class="space-y-1">
            <div class="mb-2">
                <a href="{{ route('guru.ujian.rekap', $ujian->id) }}"
                    class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-500 hover:text-blue-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Rekap Nilai</span>
                </a>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    {{ $siswa->user->name ?? $siswa->nama_lengkap ?? 'Siswa' }}
                </h1>
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-200/70 shadow-2xs">
                    Kelas {{ $ujianSiswa->pengampuKelas?->kelas?->nama_kelas ?? '-' }}
                </span>
            </div>

            <p class="text-sm text-slate-500 leading-relaxed font-normal">
                Lembar Jawaban &amp; Koreksi
                <span class="mx-1.5 text-slate-300">&bull;</span>
                Ujian: <span class="font-semibold text-slate-700">{{ $ujianSiswa->judul }}</span>
            </p>
        </div>

        {{-- KARTU NILAI AKHIR SAAT INI --}}
        <div
            class="bg-white border border-slate-200/80 rounded-2xl px-5 py-3.5 shadow-sm flex items-center gap-3.5 shrink-0 self-start sm:self-auto">
            <div
                class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100/80 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">
                    Nilai Akhir Saat Ini
                </span>
                <span class="text-2xl font-extrabold text-blue-600 leading-tight" x-text="nilaiAkhir">
                    {{ $sesi->nilai_akhir }}
                </span>
            </div>
        </div>
    </div>

    {{-- =========================================================
        DAFTAR SOAL & JAWABAN SISWA
    ========================================================== --}}
    <div class="space-y-4">
        @foreach($daftarJawaban as $item)
            @php $soal = $item['soal']; @endphp
            <div
                class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-200 space-y-4">

                {{-- Header Kartu Soal --}}
                <div class="flex items-center justify-between gap-3 flex-wrap border-b border-slate-100 pb-3.5">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200/70">
                            Soal No. {{ $item['nomor'] }}
                        </span>
                        <span
                            class="text-[11px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 border border-slate-200/60">
                            {{ str_replace('_', ' ', $soal->tipe_soal) }}
                        </span>
                    </div>

                    @if((float) $soal->bobot <= 0)
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/80 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Soal Dianulir (0 Poin)
                        </span>
                    @elseif($soal->tipe_soal !== 'essay')
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border shadow-2xs {{ $item['is_benar'] ? 'bg-emerald-50 text-emerald-700 border-emerald-200/70' : 'bg-rose-50 text-rose-600 border-rose-200/70' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ $item['is_benar'] ? 'Benar' : 'Salah' }} ({{ round($item['skor_diperoleh'], 2) }} / {{ $soal->bobot }} Poin)
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-200/70 shadow-2xs">
                            Maksimal: {{ $soal->bobot }} Poin
                        </span>
                    @endif
                </div>

                {{-- Teks Pertanyaan --}}
                <div class="text-sm text-slate-800 font-medium leading-relaxed">
                    {!! $soal->pertanyaan !!}
                </div>

                {{-- Jawaban Non-Essay --}}
                @if($soal->tipe_soal !== 'essay')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                        <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/80">
                            <span class="text-[11px] uppercase tracking-wider text-slate-400 font-bold block mb-1">
                                Jawaban Siswa
                            </span>
                            <span class="font-bold uppercase text-slate-800 text-xs sm:text-[13px]">
                                {{ $item['jawaban_teks'] ?: '(Tidak Dijawab)' }}
                            </span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-blue-50/40 border border-blue-200/70">
                            <span class="text-[11px] uppercase tracking-wider text-blue-600 font-bold block mb-1">
                                Kunci Jawaban
                            </span>
                            <span class="font-bold uppercase text-slate-900 text-xs sm:text-[13px]">
                                {{ is_array($soal->kunci_jawaban) ? implode(', ', $soal->kunci_jawaban) : $soal->kunci_jawaban }}
                            </span>
                        </div>
                    </div>
                @else
                    {{-- Bagian Essay yang Bisa Diedit Ulang Kapan Saja --}}
                    <div class="space-y-3 pt-1" x-data="{
                        skorInput: '{{ $item['sudah_dinilai'] ? $item['skor_diperoleh'] : '' }}',
                        saving: false,
                        saved: false
                    }">
                        <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 block mb-1.5">
                                Jawaban Essay Siswa
                            </span>
                            @if($item['jawaban_teks'])
                                <p class="text-slate-800 whitespace-pre-line text-sm leading-relaxed">{{ $item['jawaban_teks'] }}</p>
                            @else
                                <p class="text-slate-400 italic text-xs">(Siswa tidak mengisi jawaban)</p>
                            @endif
                        </div>

                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-blue-50/40 border border-blue-200/70">
                            <div class="flex items-center gap-3 flex-wrap">
                                <label class="text-xs font-bold text-slate-700">
                                    Skor Essay <span class="text-slate-400 font-semibold">(0 – {{ $soal->bobot }})</span>:
                                </label>
                                <input type="number" step="0.01" min="0" max="{{ $soal->bobot }}" x-model="skorInput"
                                    placeholder="0"
                                    class="w-28 rounded-xl border border-slate-200/90 bg-white px-3.5 py-2 text-sm font-extrabold text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>

                            <div class="flex items-center gap-2.5 self-end sm:self-auto">
                                <span x-show="saved" x-transition
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200/70 px-2.5 py-1 rounded-lg">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Tersimpan</span>
                                </span>

                                <button type="button" :disabled="saving || skorInput === ''"
                                    @click="
                                        saving = true; saved = false;
                                        fetch('{{ route('guru.ujian.simpan-nilai-essay', $ujian->id) }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                ujian_id: {{ $ujianSiswa->id }},
                                                siswa_id: {{ $siswa->id }},
                                                soal_id: {{ $soal->id }},
                                                skor: skorInput
                                            })
                                        })
                                        .then(r => r.json())
                                        .then(d => {
                                            if (d.status === 'success') {
                                                nilaiAkhir = d.nilai_akhir;
                                                saved = true;
                                                setTimeout(() => saved = false, 2500);
                                            }
                                        })
                                        .finally(() => saving = false);
                                    "
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition-all duration-200 shadow-xs hover:shadow-sm cursor-pointer disabled:opacity-50">
                                    <span x-text="saving ? 'Menyimpan...' : 'Simpan Nilai Essay'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<script>
function detailJawabanApp() {
    return {
        nilaiAkhir: '{{ $sesi->nilai_akhir }}'
    };
}
</script>
@endsection
