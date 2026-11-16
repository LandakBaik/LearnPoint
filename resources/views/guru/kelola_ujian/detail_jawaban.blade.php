@extends('layouts.app')

@section('title', 'Lembar Jawaban: ' . ($siswa->user->name ?? 'Siswa'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="detailJawabanApp()">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-200 shadow-xs">
        <div>
            <a href="{{ route('guru.ujian.rekap', $ujian->id) }}" class="text-xs font-semibold text-gray-500 hover:text-blue-600">
                &larr; Kembali ke Rekap Nilai
            </a>
            <h1 class="text-xl font-black text-gray-900 mt-1">
                {{ $siswa->user->name ?? $siswa->nama_lengkap ?? 'Siswa' }}
            </h1>
            <p class="text-xs text-gray-500">
                Kelas: <strong class="text-gray-800">{{ $ujianSiswa->pengampuKelas?->kelas?->nama_kelas ?? '-' }}</strong> &bull;
                Ujian: <strong class="text-gray-800">{{ $ujianSiswa->judul }}</strong>
            </p>
        </div>

        <div class="text-left sm:text-right bg-indigo-50 border border-indigo-200 px-5 py-3 rounded-2xl">
            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 block">Nilai Akhir Saat Ini</span>
            <span class="text-2xl font-black text-indigo-900" x-text="nilaiAkhir">{{ $sesi->nilai_akhir }}</span>
        </div>
    </div>

    <div class="space-y-4">
        @foreach($daftarJawaban as $item)
            @php $soal = $item['soal']; @endphp
            <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-xl text-xs font-extrabold bg-gray-100 text-gray-800">
                            Soal No. {{ $item['nomor'] }}
                        </span>
                        <span class="text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-600">
                            {{ str_replace('_', ' ', $soal->tipe_soal) }}
                        </span>
                    </div>

                    @if((float) $soal->bobot <= 0)
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-600 border border-slate-300">
                            ⊘ Soal Tidak Valid / Dianulir (0 Poin)
                        </span>
                    @elseif($soal->tipe_soal !== 'essay')
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold {{ $item['is_benar'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                            {{ $item['is_benar'] ? '✓ Benar' : '✕ Salah' }} ({{ round($item['skor_diperoleh'], 2) }} / {{ $soal->bobot }} Poin)
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                            Maksimal: {{ $soal->bobot }} Poin
                        </span>
                    @endif
                </div>

                <div class="text-sm text-gray-800 font-medium leading-relaxed">
                    {!! $soal->pertanyaan !!}
                </div>

                @if($soal->tipe_soal !== 'essay')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-2">
                        <div class="p-3 rounded-2xl bg-gray-50 border border-gray-200">
                            <span class="text-gray-400 font-bold block mb-1">Jawaban Siswa:</span>
                            <span class="font-extrabold uppercase text-gray-900">{{ $item['jawaban_teks'] ?: '(Tidak Dijawab)' }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-emerald-50/70 border border-emerald-200">
                            <span class="text-emerald-700 font-bold block mb-1">Kunci Jawaban:</span>
                            <span class="font-extrabold uppercase text-emerald-900">
                                {{ is_array($soal->kunci_jawaban) ? implode(', ', $soal->kunci_jawaban) : $soal->kunci_jawaban }}
                            </span>
                        </div>
                    </div>
                @else
                    {{-- Bagian Essay yang Bisa Diedit Ulang Kapan Saja --}}
                    <div class="space-y-3 pt-2" x-data="{
                        skorInput: '{{ $item['sudah_dinilai'] ? $item['skor_diperoleh'] : '' }}',
                        saving: false,
                        saved: false
                    }">
                        <div class="p-4 rounded-2xl bg-indigo-50/40 border border-indigo-100 text-xs">
                            <span class="font-extrabold text-indigo-700 block mb-1">Jawaban Essay Siswa:</span>
                            <p class="text-gray-900 whitespace-pre-line text-sm">{{ $item['jawaban_teks'] ?: '(Siswa tidak mengisi jawaban)' }}</p>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-amber-50/60 border border-amber-200">
                            <div class="flex items-center gap-3">
                                <label class="text-xs font-extrabold text-gray-800">Skor Essay (0 – {{ $soal->bobot }}):</label>
                                <input type="number" step="0.01" min="0" max="{{ $soal->bobot }}" x-model="skorInput"
                                    class="w-28 rounded-xl border-2 border-amber-400 px-3 py-1.5 text-sm font-black bg-white outline-none">
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-show="saved" class="text-xs font-bold text-emerald-600">&check; Tersimpan!</span>
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
                                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition cursor-pointer disabled:opacity-50">
                                    <span x-text="saving ? 'Menyimpan...' : 'Simpan / Update Nilai Essay'"></span>
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
