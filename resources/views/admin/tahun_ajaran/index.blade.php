@extends('layouts.app')

@section('title', 'Kelola Tahun Ajaran & Kenaikan Kelas')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{
    tab: '{{ $activePeriod->semester === 'ganjil' ? 'semester' : 'kenaikan' }}',
    createModalOpen: false,
    editModalOpen: false,
    editForm: {
        id: '',
        action: '',
        tahun_ajaran: '',
        semester: 'ganjil',
        tanggal_mulai: '',
        tanggal_selesai: ''
    },
    openEditModal(item) {
        this.editForm = {
            id: item.id,
            action: item.action,
            tahun_ajaran: item.tahun_ajaran,
            semester: item.semester,
            tanggal_mulai: item.tanggal_mulai || '',
            tanggal_selesai: item.tanggal_selesai || ''
        };
        this.editModalOpen = true;
    }
}">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Tahun Ajaran &amp; Kenaikan Kelas</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola periode akademik aktif, koreksi data periode, pergantian semester, dan kenaikan kelas siswa.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button"
                    @click="createModalOpen = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Periode</span>
            </button>
        </div>
    </div>

    <!-- 4 Highlight Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Periode Aktif</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">{{ $activePeriod->tahun_ajaran }}</div>
            <div class="text-[11px] font-semibold text-emerald-600 mt-1 capitalize">Semester {{ $activePeriod->semester }}</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Periode</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $totalPeriods }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Tercatat di sistem</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Siswa Periode Ini</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $activeStudentCount }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Siswa aktif di rombel</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pengampu Mapel</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $activePengampuCount }}</div>
            <div class="text-[11px] font-medium text-slate-500 mt-1">Alokasi kelas periode ini</div>
        </div>
    </div>

    <!-- Tabel Daftar Periode Akademik -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">Daftar Periode Akademik</h2>
                <p class="text-xs text-slate-500">Anda dapat mengedit nama/tanggal periode jika terjadi salah input, atau beralih ke periode lain.</p>
            </div>
            <span class="text-xs font-medium text-slate-500">Saran berikutnya: <strong class="text-blue-600">{{ $suggestedNext['label'] }}</strong></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-5">Tahun Ajaran</th>
                        <th class="py-3.5 px-4">Semester</th>
                        <th class="py-3.5 px-4">Rentang Tanggal</th>
                        <th class="py-3.5 px-4">Data Terkait</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @foreach($periods as $p)
                        @php
                            $isPast = !$p->is_active && ($p->sort_rank < $activePeriod->sort_rank);
                            $canDelete = !$p->is_active && $p->anggota_kelases_count === 0 && $p->pengampu_kelases_count === 0;
                        @endphp
                        <tr class="transition-colors {{ $p->is_active ? 'bg-blue-50/30' : 'hover:bg-slate-50/60' }}">
                            <td class="py-3.5 px-5 font-bold text-slate-900">{{ $p->tahun_ajaran }}</td>
                            <td class="py-3.5 px-4 capitalize font-semibold text-slate-700">{{ $p->semester }}</td>
                            <td class="py-3.5 px-4 text-xs text-slate-500">
                                {{ $p->tanggal_mulai ? $p->tanggal_mulai->format('d M Y') : '-' }}
                                &ndash;
                                {{ $p->tanggal_selesai ? $p->tanggal_selesai->format('d M Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                <span class="font-semibold">{{ $p->anggota_kelases_count }}</span> siswa &bull;
                                <span class="font-semibold">{{ $p->pengampu_kelases_count }}</span> pengampu
                            </td>
                            <td class="py-3.5 px-4">
                                @if($p->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @elseif($isPast)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                        Arsip (Selesai)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                        Mendatang
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button type="button"
                                            @click='openEditModal({
                                                id: {{ $p->id }},
                                                action: "{{ route("admin.tahun-ajaran.update", $p) }}",
                                                tahun_ajaran: @json($p->tahun_ajaran),
                                                semester: @json($p->semester),
                                                tanggal_mulai: @json($p->tanggal_mulai?->format("Y-m-d")),
                                                tanggal_selesai: @json($p->tanggal_selesai?->format("Y-m-d"))
                                            })'
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer">
                                        Edit
                                    </button>

                                    @if(!$p->is_active)
                                        <form method="POST" action="{{ route('admin.tahun-ajaran.activate', $p) }}"
                                              onsubmit="return confirm('{{ $isPast ? 'PERHATIAN: Periode ini sudah berlalu (Arsip). Mengaktifkan kembali periode ini akan mengubah kelas aktif seluruh siswa ke periode ini. Lanjutkan?' : 'Aktifkan periode ' . $p->label . ' sekarang?' }}');"
                                              class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer {{ $isPast ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200' : 'bg-blue-600 hover:bg-blue-700 text-white' }}">
                                                {{ $isPast ? 'Pulihkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif

                                    @if($canDelete)
                                        <form method="POST" action="{{ route('admin.tahun-ajaran.destroy', $p) }}"
                                              onsubmit="return confirm('Hapus periode {{ $p->label }}?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transisi Akademik: Pergantian Semester vs Kenaikan Kelas -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 space-y-5">
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-4">
            <button type="button" @click="tab = 'semester'"
                :class="tab === 'semester' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                1. Pergantian Semester (Ganjil &rarr; Genap)
            </button>
            <button type="button" @click="tab = 'kenaikan'"
                :class="tab === 'kenaikan' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                2. Kenaikan Kelas &amp; Kelulusan (Tahun Ajaran Baru)
            </button>
        </div>

        <!-- Tab 1: Pergantian Semester -->
        <div x-show="tab === 'semester'" class="space-y-4">
            <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-100 text-xs text-blue-900 leading-relaxed">
                Gunakan fitur ini saat berpindah dari semester <strong>Ganjil ke Genap</strong> di tahun ajaran yang sama. Seluruh siswa aktif pada kelas saat ini akan disalin ke periode semester baru tanpa menghapus histori semester sebelumnya.
            </div>

            <form method="POST" action="{{ route('admin.tahun-ajaran.copy-semester') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Dari Periode Asal</label>
                    <select name="source_period_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" {{ $p->id === $activePeriod->id ? 'selected' : '' }}>{{ $p->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Ajaran Tujuan</label>
                    <input type="text" name="tahun_ajaran" value="{{ $activePeriod->tahun_ajaran }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Semester Tujuan</label>
                    <select name="semester" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm">
                        <option value="genap" {{ $activePeriod->semester === 'ganjil' ? 'selected' : '' }}>Genap</option>
                        <option value="ganjil" {{ $activePeriod->semester === 'genap' ? 'selected' : '' }}>Ganjil</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition cursor-pointer">
                        Salin Rombel &amp; Aktifkan
                    </button>
                </div>
                <div class="md:col-span-4">
                    <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                        <input type="checkbox" name="copy_pengampu" value="1" checked class="rounded border-slate-300 text-blue-600">
                        <span>Ikut salin penugasan Guru Pengampu Mapel ke semester tujuan</span>
                    </label>
                </div>
            </form>
        </div>

        <!-- Tab 2: Kenaikan Kelas -->
        <div x-show="tab === 'kenaikan'" style="display: none;" class="space-y-4">
            <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-100 text-xs text-emerald-900 leading-relaxed">
                Gunakan fitur ini saat pergantian <strong>Tahun Ajaran Baru</strong> (misal <code>2026/2027 Genap &rarr; 2027/2028 Ganjil</code>). Petakan setiap kelas ke kelas tingkat berikutnya atau pilih <strong>Lulus / Alumni</strong> untuk kelas 9. Siswa yang dicentang <em>Tinggal Kelas</em> akan tetap di kelas asalnya pada tahun ajaran baru.
            </div>

            <form method="POST" action="{{ route('admin.tahun-ajaran.promote') }}" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Dari Periode Asal</label>
                        <select name="source_period_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm">
                            @foreach($periods as $p)
                                <option value="{{ $p->id }}" {{ $p->id === $activePeriod->id ? 'selected' : '' }}>{{ $p->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Ajaran Tujuan</label>
                        <input type="text" name="tahun_ajaran" value="{{ $suggestedNext['semester'] === 'ganjil' ? $suggestedNext['tahun_ajaran'] : (explode('/', $activePeriod->tahun_ajaran)[1] ?? date('Y')) . '/' . (((int) (explode('/', $activePeriod->tahun_ajaran)[1] ?? date('Y'))) + 1) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Semester Tujuan</label>
                        <select name="semester" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm">
                            <option value="ganjil" selected>Ganjil</option>
                            <option value="genap">Genap</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500">
                                <th class="py-3 px-4">Kelas Asal</th>
                                <th class="py-3 px-4">Siswa Aktif</th>
                                <th class="py-3 px-4">Tujuan di Tahun Baru</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($kelases as $k)
                                <tr>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $k->nama_kelas }} <span class="text-slate-400 font-normal">(Tingkat {{ $k->tingkatan }})</span></td>
                                    <td class="py-3 px-4 font-semibold text-slate-700">{{ $sourceCounts[$k->id] ?? 0 }} siswa</td>
                                    <td class="py-3 px-4">
                                        <select name="mapping[{{ $k->id }}]" class="w-full max-w-xs px-3 py-2 rounded-xl border border-slate-200 text-xs">
                                            <option value="">-- Lewati / Jangan Pindahkan --</option>
                                            @if((int) $k->tingkatan >= 9)
                                                <option value="lulus" selected>Lulus / Alumni (Nonaktifkan dari Rombel Aktif)</option>
                                            @else
                                                <option value="lulus">Lulus / Alumni</option>
                                            @endif
                                            @foreach($kelases as $targetK)
                                                @php
                                                    $autoMatch = ((int) $targetK->tingkatan === (int) $k->tingkatan + 1)
                                                        && (preg_replace('/^\d+/', '', $targetK->nama_kelas) === preg_replace('/^\d+/', '', $k->nama_kelas));
                                                @endphp
                                                <option value="{{ $targetK->id }}" {{ $autoMatch ? 'selected' : '' }}>
                                                    {{ $targetK->nama_kelas }} (Tingkat {{ $targetK->tingkatan }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($sourceStudents->isNotEmpty())
                    <details class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                        <summary class="cursor-pointer text-xs font-bold text-slate-700">
                            Pengecualian Siswa Tinggal Kelas (Opsional &bull; {{ $sourceStudents->count() }} siswa aktif)
                        </summary>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 max-h-60 overflow-y-auto">
                            @foreach($sourceStudents as $ak)
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-200 text-xs">
                                    <input type="checkbox" name="stay_student_ids[]" value="{{ $ak->siswa_id }}" class="rounded border-slate-300 text-amber-600">
                                    <span class="truncate">
                                        <strong>{{ $ak->siswa?->nama_siswa }}</strong>
                                        <span class="text-slate-400">({{ $ak->kelas?->nama_kelas }})</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endif

                <div class="flex justify-end">
                    <button type="submit" onclick="return confirm('Proses kenaikan kelas & aktifkan tahun ajaran baru? Histori tahun ajaran lama tetap tersimpan aman.');"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition cursor-pointer">
                        Proses Kenaikan Kelas &amp; Aktifkan Periode Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Periode -->
    <div x-show="createModalOpen" style="display: none;" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" @click.self="createModalOpen = false">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Tambah Periode Akademik</h3>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.tahun-ajaran.store') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" value="{{ old('tahun_ajaran', $suggestedNext['tahun_ajaran']) }}" placeholder="2026/2027" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Semester</label>
                    <select name="semester" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="ganjil" {{ old('semester', $suggestedNext['semester']) === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                        <option value="genap" {{ old('semester', $suggestedNext['semester']) === 'genap' ? 'selected' : '' }}>Genap</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-xs text-slate-600 pt-1">
                    <input type="checkbox" name="activate" value="1" class="rounded border-slate-300 text-blue-600">
                    <span>Langsung aktifkan setelah dibuat</span>
                </label>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Periode -->
    <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" @click.self="editModalOpen = false">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Edit Periode Akademik</h3>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700">&times;</button>
            </div>
            <form method="POST" :action="editForm.action" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" x-model="editForm.tahun_ajaran" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Semester</label>
                    <select name="semester" x-model="editForm.semester" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="ganjil">Ganjil</option>
                        <option value="genap">Genap</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" x-model="editForm.tanggal_mulai" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" x-model="editForm.tanggal_selesai" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
