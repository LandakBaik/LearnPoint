<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Ujian;
use App\Models\Soal;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\GuruMapel;

class UjianController extends Controller
{
    /**
     * Menampilkan semua ujian milik guru yang sedang login.
     */
    public function index(Request $request)
    {
        $guruId = auth()->user()->guru_id;

        // Ambil seluruh ujian milik guru.
        $semuaUjian = Ujian::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
        ])
            ->whereHas('pengampuKelas.guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->get();

        // Sinkronisasi status setiap ujian.
        $semuaUjian->each(function ($ujian) {
            $ujian->sinkronisasiStatus();
        });

        // Kelompokkan ujian berdasarkan ID grup.
        // Jika tidak memiliki ID grup, perlakukan sebagai grup tersendiri.
        $grupUjians = $semuaUjian->groupBy(function ($ujian) {
            return $ujian->id_grup_ujian
                ? 'grup-' . $ujian->id_grup_ujian
                : 'ujian-' . $ujian->id;
        });



        // Ambil satu ujian sebagai perwakilan setiap grup.
        $daftarUjian = $grupUjians->map(function ($anggotaGrup) {
            $ujian = $anggotaGrup->sortByDesc('created_at')->first();

            // Simpan seluruh anggota grup agar kelasnya bisa ditampilkan di Blade.
            $ujian->kelasDalamGrup = $anggotaGrup;

            return $ujian;
        })->values();

        // Pencarian berdasarkan judul.
        if ($request->filled('search')) {
            $kataKunci = strtolower($request->search);

            $daftarUjian = $daftarUjian->filter(function ($ujian) use ($kataKunci) {
                return str_contains(
                    strtolower($ujian->judul ?? ''),
                    $kataKunci
                );
            })->values();
        }

        // Filter status.
        if ($request->filled('status')) {
            $daftarUjian = $daftarUjian
                ->where('status', $request->status)
                ->values();
        }

        // Ambil seluruh kelas yang diampu oleh guru.
        $daftarKelas = PengampuKelas::with('kelas')
            ->whereHas('guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->get()
            ->pluck('kelas')
            ->filter()
            ->unique('id')
            ->sortBy('nama_kelas')
            ->values();

        // Filter berdasarkan kelas.
        // Jika kelas cocok dengan salah satu anggota grup,
        // seluruh grup tetap ditampilkan.

        if ($request->filled('kelas_id')) {
            $kelasId = (string) $request->kelas_id;

            $daftarUjian = $daftarUjian->filter(function ($ujian) use ($kelasId) {
                return $ujian->kelasDalamGrup->contains(function ($anggota) use ($kelasId) {
                    return (string) $anggota->pengampuKelas?->kelas?->id === $kelasId;
                });
            })->values();
        }

        // Urutkan dari yang terbaru.
        $daftarUjian = $daftarUjian
            ->sortByDesc('created_at')
            ->values();

        // Pagination manual karena data sudah dikelompokkan.
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();

        $ujians = new \Illuminate\Pagination\LengthAwarePaginator(
            $daftarUjian->forPage($currentPage, $perPage)->values(),
            $daftarUjian->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Statistik dihitung berdasarkan jumlah grup, bukan jumlah baris ujian.
        // Statistik dihitung berdasarkan jumlah grup, bukan jumlah baris ujian.
        $stats = [
            'total' => $grupUjians->count(),

            'draft' => $grupUjians->filter(function ($anggotaGrup) {
                return $anggotaGrup->first()->status === 'draft';
            })->count(),

            'scheduled' => $grupUjians->filter(function ($anggotaGrup) {
                return $anggotaGrup->first()->status === 'scheduled';
            })->count(),

            'published' => $grupUjians->filter(function ($anggotaGrup) {
                return $anggotaGrup->first()->status === 'published';
            })->count(),

            'completed' => $grupUjians->filter(function ($anggotaGrup) {
                return $anggotaGrup->first()->status === 'completed';
            })->count(),

            'unpublished' => $grupUjians->filter(function ($anggotaGrup) {
                return $anggotaGrup->first()->status === 'unpublished';
            })->count(),
        ];

        $modeJadwalkan = request()->query('aksi') === 'jadwalkan';

        return view('guru.kelola_ujian.index', compact(
            'ujians',
            'stats',
            'daftarKelas'
        ));
    }
    /**
     * Mengirim status dan statistik ujian terbaru untuk pembaruan otomatis.
     */
    public function statusRealtime()
    {
        $guruId = auth()->user()->guru_id;

        // Ambil seluruh ujian milik guru.
        $ujians = Ujian::whereHas('pengampuKelas.guruMapel', function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        })->get();

        // Sinkronkan status berdasarkan waktu terbaru.
        $ujians->each(function ($ujian) {
            $ujian->sinkronisasiStatus();
        });

        // Kelompokkan berdasarkan ID grup.
        $grupUjians = $ujians->groupBy(function ($ujian) {
            return $ujian->id_grup_ujian
                ? 'grup-' . $ujian->id_grup_ujian
                : 'ujian-' . $ujian->id;
        });

        // Ambil satu ujian perwakilan dari setiap grup.
        $dataUjian = $grupUjians->map(function ($anggotaGrup) {
            $ujian = $anggotaGrup->sortByDesc('created_at')->first();

            return [
                'id' => $ujian->id,
                'id_grup_ujian' => $ujian->id_grup_ujian,
                'status' => $ujian->status,
                'status_label' => $ujian->status_label,
                'jumlah_kelas' => $anggotaGrup->count(),
            ];
        })->values();

        // Hitung statistik berdasarkan jumlah grup.
        $stats = [
            'total' => $grupUjians->count(),

            'draft' => $grupUjians->filter(function ($anggotaGrup) {
                return $anggotaGrup->first()->status === 'draft';
            })->count(),

            'scheduled' => $grupUjians->filter(function ($anggotaGrup) {
                return $anggotaGrup->first()->status === 'scheduled';
            })->count(),

            'published' => $grupUjians->filter(function ($anggotaGrup) {
                return $anggotaGrup->first()->status === 'published';
            })->count(),

            'completed' => $grupUjians->filter(function ($anggotaGrup) {
                return $anggotaGrup->first()->status === 'completed';
            })->count(),

            'unpublished' => $grupUjians->filter(function ($anggotaGrup) {
                return $anggotaGrup->first()->status === 'unpublished';
            })->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'ujians' => $dataUjian,
        ]);
    }


    /**
     * Menampilkan halaman tambah ujian.
     */
    public function create()
    {
        $guruId = auth()->user()->guru_id;

        // Ambil seluruh kelas yang diampu guru beserta mapelnya.
        $pengampuKelases = PengampuKelas::with([
            'guruMapel.mapel',
            'kelas',
        ])
            ->whereHas('guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->get();

        // Susun data khusus untuk pemilihan bertingkat.
        $dataMapel = $pengampuKelases
            ->filter(fn($item) => $item->guruMapel?->mapel && $item->kelas)
            ->groupBy('guruMapel.mapel_id')
            ->map(function ($items, $mapelId) {
                $mapel = $items->first()->guruMapel->mapel;

                return [
                    'id' => (int) $mapelId,
                    'nama' => $mapel->nama_mapel,

                    'tingkatan' => $items
                        ->groupBy('kelas.tingkatan')
                        ->map(function ($kelasItems, $tingkatan) {
                            return [
                                'nama' => (string) $tingkatan,
                                'kelas' => $kelasItems
                                    ->map(function ($item) {
                                        return [
                                            'pengampu_kelas_id' => $item->id,
                                            'nama_kelas' => $item->kelas->nama_kelas,
                                        ];
                                    })
                                    ->unique('pengampu_kelas_id')
                                    ->values()
                                    ->all(),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        return view('guru.kelola_ujian.create', compact(
            'pengampuKelases',
            'dataMapel'
        ));
    }




    public function store(Request $request)
    {
        $guruId = auth()->user()->guru_id;

        // Tentukan aksi dari tombol yang ditekan.
        $action = $request->input('action', 'draft');

        if (!in_array($action, ['draft', 'schedule', 'publish'], true)) {
            abort(400, 'Aksi ujian tidak valid.');
        }

        // Validasi data utama dan soal.
        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:100',
                'regex:/^[\pL\pN ]+$/u',
            ],
            'deskripsi' => ['nullable', 'string'],
            'kisi_kisi' => ['nullable', 'string', 'max:500'],

            'mapel_id' => [
                'required',
                'integer',
            ],


            // Sekarang guru dapat memilih beberapa kelas.
            'pengampu_kelas_id' => ['required', 'array', 'min:1'],
            'pengampu_kelas_id.*' => [
                'required',
                'integer',
                'distinct',
            ],

            'durasi_menit' => [
                'required',
                'integer',
                'min:1',
                'max:999',
            ],
            'kkm' => [
                'required',
                'integer',
                'min:0',
                'max:99',
            ],

            'waktu_mulai' => $action === 'schedule'
                ? ['required', 'date', 'after:now', 'before_or_equal:+1 year']
                : ['nullable', 'date', 'after_or_equal:today', 'before_or_equal:+1 year'],

            'deadline' => $action === 'schedule'
                ? ['required', 'date', 'after:waktu_mulai', 'before_or_equal:+1 year']
                : ['nullable', 'date', 'after:waktu_mulai', 'before_or_equal:+1 year'],

            'soals' => ['required', 'array', 'min:1', 'max:100'],
            'soals.*.pertanyaan' => ['required', 'string', 'max:500'],
            'soals.*.tipe_soal' => [
                'required',
                'in:single_choice,multiple_choice,essay,matching',
            ],
            'soals.*.pilihan' => ['nullable', 'array', 'max:6'],
            'soals.*.kunci_jawaban' => ['nullable'],
            'soals.*.bobot' => ['required', 'integer', 'min:0', 'max:99'],
            'soals.*.urutan' => ['nullable', 'integer', 'min:1'],
            'soals.*.gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:2048',
            ],
        ]);

        // Ambil mapel dan kelas yang dipilih.
        $mapelId = (int) $validated['mapel_id'];
        $kelasIds = $validated['pengampu_kelas_id'];

        // Pastikan mapel benar-benar diampu guru yang sedang login.
        $mapelDimilikiGuru = GuruMapel::where('guru_id', $guruId)
            ->where('mapel_id', $mapelId)
            ->exists();

        if (!$mapelDimilikiGuru) {
            return back()
                ->withInput()
                ->withErrors([
                    'mapel_id' => 'Mata pelajaran tidak valid atau bukan milikmu.',
                ]);
        }

        // Pastikan seluruh kelas berasal dari mapel yang dipilih
// dan benar-benar diampu guru yang sedang login.
        $pengampuKelases = PengampuKelas::whereIn('id', $kelasIds)
            ->whereHas('guruMapel', function ($query) use ($guruId, $mapelId) {
                $query->where('guru_id', $guruId)
                    ->where('mapel_id', $mapelId);
            })
            ->whereHas('kelas')
            ->get();

        // Tolak jika ada kelas yang tidak cocok.
        if ($pengampuKelases->count() !== count($kelasIds)) {
            return back()
                ->withInput()
                ->withErrors([
                    'pengampu_kelas_id' =>
                        'Ada kelas yang tidak sesuai dengan mata pelajaran atau tidak dapat kamu pilih.',
                ]);
        }

        // Total bobot soal tidak boleh lebih dari 100.
        $totalBobot = collect($validated['soals'])
            ->sum(fn($soal) => (int) ($soal['bobot'] ?? 0));

        if ($totalBobot > 100) {
            return back()
                ->withInput()
                ->withErrors([
                    'soals' => "Total bobot soal adalah {$totalBobot}. Maksimal 100 poin.",
                ]);
        }

        // Tentukan waktu dan status berdasarkan aksi.
        if ($action === 'publish') {
            $waktuMulai = now();
            $deadlineUjian = $waktuMulai->copy()->addDay();
            $status = 'published';

        } elseif ($action === 'schedule') {
            $waktuMulai = Carbon::parse($validated['waktu_mulai']);
            $deadlineUjian = Carbon::parse($validated['deadline']);

            // Jadwal harus berada antara pukul 07.00–17.00.
            foreach ([$waktuMulai, $deadlineUjian] as $waktu) {
                if (
                    $waktu->format('H:i') < '07:00' ||
                    $waktu->format('H:i') > '17:00'
                ) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'waktu_mulai' => 'Jadwal ujian harus berada antara pukul 07.00 sampai 17.00.',
                        ]);
                }
            }

            $status = 'scheduled';

        } else {
            $waktuMulai = !empty($validated['waktu_mulai'])
                ? Carbon::parse($validated['waktu_mulai'])
                : null;

            $deadlineUjian = !empty($validated['deadline'])
                ? Carbon::parse($validated['deadline'])
                : null;

            $status = 'draft';
        }

        // Validasi pilihan dan kunci jawaban.
        foreach ($validated['soals'] as $index => $data) {
            $tipe = $data['tipe_soal'];

            if (!in_array($tipe, ['single_choice', 'multiple_choice'], true)) {
                continue;
            }

            $pilihan = $data['pilihan'] ?? [];
            $kunci = $this->normalizeKunciPilihan(
                $data['kunci_jawaban'] ?? []
            );

            if (count($pilihan) < 2 || count($pilihan) > 6) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'soals' => 'Soal nomor ' . ($index + 1) .
                            ' harus memiliki 2 sampai 6 pilihan jawaban.',
                    ]);
            }

            foreach ($pilihan as $pilihanText) {
                if (trim((string) $pilihanText) === '') {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'soals' => 'Semua pilihan jawaban pada soal nomor ' .
                                ($index + 1) . ' harus diisi.',
                        ]);
                }
            }

            if (empty($kunci)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'soals' => 'Soal nomor ' . ($index + 1) .
                            ' belum memiliki kunci jawaban.',
                    ]);
            }

            if ($tipe === 'single_choice' && count($kunci) !== 1) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'soals' => 'Soal pilihan tunggal nomor ' .
                            ($index + 1) . ' hanya boleh memiliki satu jawaban.',
                    ]);
            }

            foreach ($kunci as $jawaban) {
                $posisi = ord($jawaban) - 97;

                if (!isset($pilihan[$posisi])) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'soals' => 'Kunci jawaban soal nomor ' .
                                ($index + 1) . ' tidak sesuai dengan pilihan yang tersedia.',
                        ]);
                }
            }
        }

        // Satu ID grup untuk seluruh kelas yang dipilih.
        $grupUjianId = (string) Str::uuid();

        DB::transaction(function () use ($request, $validated, $pengampuKelases, $status, $action, $waktuMulai, $deadlineUjian, $grupUjianId) {
            // Simpan gambar satu kali untuk setiap soal.
            $dataSoals = [];

            foreach ($request->input('soals', []) as $index => $data) {
                $gambar = null;

                if ($request->hasFile("soals.$index.gambar")) {
                    $gambar = $request->file("soals.$index.gambar")
                        ->store('soal-images', 'public');
                }

                $pilihan = $data['pilihan'] ?? [];
                $kunci = $data['kunci_jawaban'] ?? [];

                if (!is_array($pilihan)) {
                    $pilihan = [];
                }

                if (!is_array($kunci)) {
                    $kunci = [$kunci];
                }

                $kunci = $this->normalizeKunciPilihan($kunci);

                $kunci = array_values(array_filter(
                    $kunci,
                    fn($value) => $value !== null && $value !== ''
                ));

                $dataSoals[] = [
                    'pertanyaan' => $data['pertanyaan'],
                    'tipe_soal' => $data['tipe_soal'],
                    'pilihan' => $pilihan,
                    'kunci_jawaban' => $kunci,
                    'gambar' => $gambar,
                    'bobot' => $data['bobot'] ?? 1,
                    'urutan' => $data['urutan'] ?? ($index + 1),
                ];
            }

            // Buat satu ujian untuk setiap kelas.
            foreach ($pengampuKelases as $pengampuKelas) {
                $ujian = Ujian::create([
                    'id_grup_ujian' => $grupUjianId,
                    'judul' => $validated['judul'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
                    'kisi_kisi' => $validated['kisi_kisi'] ?? null,
                    'pengampu_kelas_id' => $pengampuKelas->id,
                    'durasi_menit' => $validated['durasi_menit'],
                    'kkm' => $validated['kkm'],
                    'waktu_mulai' => $waktuMulai,
                    'deadline' => $deadlineUjian,
                    'status' => $status,
                    'dipublikasi_at' => $action === 'publish'
                        ? now()
                        : null,
                ]);

                // Salin soal ke ujian milik kelas ini.
                foreach ($dataSoals as $dataSoal) {
                    Soal::create([
                        'pertanyaan' => $dataSoal['pertanyaan'],
                        'tipe_soal' => $dataSoal['tipe_soal'],
                        'pilihan' => $dataSoal['pilihan'],
                        'kunci_jawaban' => $dataSoal['kunci_jawaban'],
                        'gambar' => $dataSoal['gambar'],
                        'bobot' => $dataSoal['bobot'],
                        'urutan' => $dataSoal['urutan'],
                        'ujian_id' => $ujian->id,
                        'quiz_id' => null,
                        'tugas_id' => null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('guru.ujian.index')
            ->with('success', 'Ujian berhasil disimpan untuk ' .
                $pengampuKelases->count() . ' kelas.');
    }


    public function show(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $ujian->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
            'seleksiUjians',
        ]);

        return view('guru.kelola_ujian.show', compact('ujian'));
    }


    /**
     * Menampilkan halaman edit.
     */
    public function edit(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $guruId = auth()->user()->guru_id;

        // Ambil seluruh ujian yang berada dalam grup yang sama.
        $queryGrup = Ujian::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
        ])->whereHas('pengampuKelas.guruMapel', function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        });

        // Jika ID grup tersedia, ambil semua anggota grup.
        // Jika tidak, gunakan ujian yang sedang dibuka saja.
        if ($ujian->id_grup_ujian) {
            $grupUjians = $queryGrup
                ->where('id_grup_ujian', $ujian->id_grup_ujian)
                ->get();
        } else {
            $grupUjians = $queryGrup
                ->whereKey($ujian->id)
                ->get();
        }

        // Ambil data pengampu kelas dari anggota grup.
        // Data ini digunakan untuk dropdown pemilihan satu kelas.
        $pengampuKelases = $grupUjians
            ->pluck('pengampuKelas')
            ->filter()
            ->unique('id')
            ->values();

        $ujian->load('soals');

        // Siapkan data soal untuk dikirim ke JavaScript.
        $soalData = $ujian->soals->map(function ($soal) {
            return [
                'id' => $soal->id,
                'localId' => 'existing-' . $soal->id,

                'pertanyaan' => $soal->pertanyaan,
                'tipe_soal' => $soal->tipe_soal,

                'pilihan' => is_array($soal->pilihan)
                    ? array_values($soal->pilihan)
                    : [],

                'kunci_jawaban' => is_array($soal->kunci_jawaban)
                    ? array_values($soal->kunci_jawaban)
                    : [],

                'kunci_jawaban_text' => is_array($soal->kunci_jawaban)
                    ? implode("\n", $soal->kunci_jawaban)
                    : ($soal->kunci_jawaban ?? ''),

                'gambar' => $soal->gambar
                    ? asset('storage/' . $soal->gambar)
                    : null,

                'bobot' => (float) $soal->bobot,
            ];
        })->values()->all();

        $modeJadwalkan = request()->query('aksi') === 'jadwalkan';

        return view('guru.kelola_ujian.edit', compact(
            'ujian',
            'pengampuKelases',
            'grupUjians',
            'soalData',
            'modeJadwalkan'
        ));
    }


    /**
     * Update ujian beserta soal.
     */

    public function update(Request $request, Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        // Jangan mengedit ujian yang sudah dipublikasi atau selesai.
        if (!$ujian->bolehDiedit()) {
            return back()->with(
                'error',
                'Ujian harus ditarik dari publikasi terlebih dahulu sebelum diedit.'
            );
        }

        $modeJadwalkan = $request->query('aksi') === 'jadwalkan';

        $validated = $request->validate([
            'mode_edit' => ['required', 'in:semua,satu'],

            'pengampu_kelas_id' => [
                'nullable',
                'required_if:mode_edit,satu',
                'integer',
            ],

            'judul' => ['required', 'string', 'max:100', 'regex:/^[\pL\pN ]+$/u'],
            'deskripsi' => ['nullable', 'string'],
            'kisi_kisi' => ['nullable', 'string', 'max:500'],

            'durasi_menit' => ['required', 'integer', 'min:1', 'max:999'],
            'kkm' => ['required', 'integer', 'min:0', 'max:99'],

            'waktu_mulai' => [
                $modeJadwalkan ? 'required' : 'nullable',
                'date',
                ...($modeJadwalkan ? ['after_or_equal:today'] : []),
                'before_or_equal:' . now()->addYear()->toDateString(),
            ],

            'deadline' => [
                $modeJadwalkan ? 'required' : 'nullable',
                'date',
                'after:waktu_mulai',
                ...($modeJadwalkan ? ['after_or_equal:today'] : []),
                'before_or_equal:' . now()->addYear()->toDateString(),
            ],
            'soals' => ['required', 'array', 'min:1', 'max:100'],
            'soals.*.id' => ['nullable', 'integer'],
            'soals.*.pertanyaan' => ['required', 'string', 'max:500'],
            'soals.*.tipe_soal' => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'soals.*.pilihan' => ['nullable', 'array', 'max:6'],
            'soals.*.kunci_jawaban' => ['nullable'],
            'soals.*.kunci_jawaban_text' => ['nullable', 'string'],
            'soals.*.bobot' => ['required', 'integer', 'min:0', 'max:99'],
            'soals.*.urutan' => ['nullable', 'integer', 'min:1'],
            'soals.*.gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ]);

        $guruId = auth()->user()->guru_id;

        // Ambil semua ujian dalam grup yang sama.
        $grup = Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)
            ->orderBy('id')
            ->get();

        if ($grup->isEmpty()) {
            $grup = collect([$ujian]);
        }

        // Pastikan semua ujian dalam grup masih bisa diedit.
        foreach ($grup as $anggota) {
            $this->authorizeGuruUjian($anggota);

            if (!$anggota->bolehDiedit()) {
                return back()->with(
                    'error',
                    'Ada ujian dalam grup yang belum ditarik dari publikasi.'
                );
            }
        }

        // Pilih target kelas.
        if ($validated['mode_edit'] === 'semua') {
            $targetUjians = $grup;
        } else {
            $targetUjians = $grup->where(
                'pengampu_kelas_id',
                $validated['pengampu_kelas_id']
            );

            if ($targetUjians->isEmpty()) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'pengampu_kelas_id' => 'Kelas tersebut tidak ditemukan dalam grup ujian ini.',
                    ]);
            }
        }

        // Validasi bahwa seluruh target masih milik guru.
        foreach ($targetUjians as $target) {
            $this->authorizeGuruUjian($target);
        }

        // Validasi pilihan dan kunci jawaban.
        foreach ($validated['soals'] as $index => $data) {
            $tipe = $data['tipe_soal'];

            if (!in_array($tipe, ['single_choice', 'multiple_choice'], true)) {
                continue;
            }

            $pilihan = $data['pilihan'] ?? [];
            $kunci = $this->normalizeKunciPilihan(
                $data['kunci_jawaban'] ?? []
            );

            if (count($pilihan) < 2 || count($pilihan) > 6) {
                return back()->withInput()->withErrors([
                    'soals' => 'Soal nomor ' . ($index + 1) .
                        ' harus memiliki 2 sampai 6 pilihan jawaban.',
                ]);
            }

            foreach ($pilihan as $pilihanText) {
                if (trim((string) $pilihanText) === '') {
                    return back()->withInput()->withErrors([
                        'soals' => 'Semua pilihan jawaban pada soal nomor ' .
                            ($index + 1) . ' harus diisi.',
                    ]);
                }
            }

            if (empty($kunci)) {
                return back()->withInput()->withErrors([
                    'soals' => 'Soal nomor ' . ($index + 1) .
                        ' belum memiliki kunci jawaban.',
                ]);
            }

            if ($tipe === 'single_choice' && count($kunci) !== 1) {
                return back()->withInput()->withErrors([
                    'soals' => 'Soal pilihan tunggal nomor ' .
                        ($index + 1) . ' hanya boleh memiliki satu jawaban.',
                ]);
            }

            foreach ($kunci as $jawaban) {
                $posisi = ord($jawaban) - 97;

                if (!isset($pilihan[$posisi])) {
                    return back()->withInput()->withErrors([
                        'soals' => 'Kunci jawaban soal nomor ' .
                            ($index + 1) . ' tidak sesuai dengan pilihan yang tersedia.',
                    ]);
                }
            }
        }

        $totalBobot = collect($validated['soals'])
            ->sum(fn($soal) => (int) $soal['bobot']);

        if ($totalBobot > 100) {
            return back()->withInput()->withErrors([
                'soals' => "Total bobot soal adalah {$totalBobot}. Maksimal 100 poin.",
            ]);
        }

        // Validasi jam ujian.
        foreach (['waktu_mulai', 'deadline'] as $field) {
            if ($request->filled($field)) {
                $jam = Carbon::parse($request->input($field));

                if ($jam->format('H:i') < '07:00' || $jam->format('H:i') > '17:00') {
                    return back()->withInput()->withErrors([
                        $field => 'Waktu ujian harus antara pukul 07.00 sampai 17.00.',
                    ]);
                }
            }
        }

        // Tentukan status berdasarkan jadwal.
        $status = 'draft';

        if (!empty($validated['waktu_mulai']) && !empty($validated['deadline'])) {
            $mulai = Carbon::parse($validated['waktu_mulai']);
            $deadline = Carbon::parse($validated['deadline']);

            if (now()->greaterThan($deadline)) {
                $status = 'completed';
            } elseif (now()->lt($mulai)) {
                $status = 'scheduled';
            } else {
                $status = 'published';
            }
        }

        DB::transaction(function () use ($request, $validated, $targetUjians, $status) {
            // Mode satu kelas: pisahkan kelas dari grup sebelumnya.
            if ($validated['mode_edit'] === 'satu') {
                $targetUjians->first()->update([
                    'id_grup_ujian' => (string) \Illuminate\Support\Str::uuid(),
                ]);
            }

            // Soal dari ujian yang sedang diedit menjadi sumber gambar lama.
            $soalSumber = $targetUjians->first()
                ->soals()
                ->get()
                ->keyBy('urutan');

            // Siapkan seluruh data soal satu kali.
            $dataSoals = [];

            foreach ($request->input('soals', []) as $index => $data) {
                $urutan = (int) ($data['urutan'] ?? ($index + 1));

                $soalLama = $soalSumber->get($urutan);

                $gambar = $soalLama?->gambar;

                if ($request->hasFile("soals.$index.gambar")) {
                    $gambar = $request
                        ->file("soals.$index.gambar")
                        ->store('soal-images', 'public');
                }

                $pilihan = $data['pilihan'] ?? [];

                $pilihan = is_array($pilihan)
                    ? array_values(array_filter(
                        $pilihan,
                        fn($value) => $value !== null && trim((string) $value) !== ''
                    ))
                    : [];

                $kunciJawaban = $data['kunci_jawaban'] ?? [];

                if (!is_array($kunciJawaban)) {
                    $kunciJawaban = [$kunciJawaban];
                }

                $kunciJawaban = array_values(array_filter(
                    $kunciJawaban,
                    fn($value) => $value !== null && trim((string) $value) !== ''
                ));

                if (in_array($data['tipe_soal'], ['single_choice', 'multiple_choice'], true)) {
                    $kunciJawaban = $this->normalizeKunciPilihan($kunciJawaban);
                }

                if ($data['tipe_soal'] === 'essay') {
                    $jawabanEssay = trim((string) ($data['kunci_jawaban_text'] ?? ''));
                    $kunciJawaban = $jawabanEssay !== '' ? [$jawabanEssay] : [];
                }

                $dataSoals[] = [
                    'pertanyaan' => $data['pertanyaan'],
                    'tipe_soal' => $data['tipe_soal'],
                    'pilihan' => $pilihan,
                    'kunci_jawaban' => $kunciJawaban,
                    'gambar' => $gambar,
                    'bobot' => $data['bobot'],
                    'urutan' => $urutan,
                    'quiz_id' => null,
                    'tugas_id' => null,
                ];
            }

            // Update semua ujian target beserta salinan soalnya.
            foreach ($targetUjians as $target) {
                $target->update([
                    'judul' => $validated['judul'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
                    'kisi_kisi' => $validated['kisi_kisi'] ?? null,
                    'durasi_menit' => $validated['durasi_menit'],
                    'kkm' => $validated['kkm'],
                    'waktu_mulai' => $validated['waktu_mulai'] ?? null,
                    'deadline' => $validated['deadline'] ?? null,
                    'status' => $status,
                    'dipublikasi_at' => $status === 'published'
                        ? ($target->dipublikasi_at ?? now())
                        : ($status === 'completed' ? $target->dipublikasi_at : null),
                ]);

                $soalLamaTarget = $target->soals()->get();

                // Hapus soal lama dan buat salinan sesuai editor.
                foreach ($soalLamaTarget as $soal) {
                    $soal->delete();
                }

                foreach ($dataSoals as $soalData) {
                    $target->soals()->create($soalData);
                }
            }
        });

        $ujianTujuan = $targetUjians->first();

        return redirect()
            ->route('guru.ujian.show', $ujianTujuan)
            ->with('success', 'Ujian berhasil diperbarui.');
    }

    public function publish(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);



        // Ambil seluruh anggota grup ujian.
        if ($ujian->id_grup_ujian) {
            $grupUjians = Ujian::where(
                'id_grup_ujian',
                $ujian->id_grup_ujian
            )->get();
        } else {
            $grupUjians = collect([$ujian]);
        }

        // Pastikan seluruh anggota grup milik guru yang sedang login.
        foreach ($grupUjians as $anggota) {
            $this->authorizeGuruUjian($anggota);
        }

        // Jadwal harus tersedia.
        if (
            $grupUjians->contains(function ($anggota) {
                return !$anggota->waktu_mulai || !$anggota->deadline;
            })
        ) {
            return back()->withErrors([
                'jadwal' => 'Atur waktu mulai dan deadline sebelum publikasi.',
            ]);
        }



        // Gunakan jadwal ujian perwakilan.
        $mulai = Carbon::parse($ujian->waktu_mulai);
        $deadline = Carbon::parse($ujian->deadline);

        if (now()->lt($mulai)) {
            $status = 'scheduled';
            $pesan = 'Ujian berhasil dijadwalkan untuk semua kelas.';
        } elseif (now()->lt($deadline)) {
            $status = 'published';
            $pesan = 'Ujian berhasil dipublikasi untuk semua kelas.';
        } else {
            $status = 'completed';
            $pesan = 'Masa ujian telah berakhir.';
        }

        // Perbarui semua anggota grup sekaligus.
        DB::transaction(function () use ($grupUjians, $status) {
            foreach ($grupUjians as $anggota) {
                $anggota->update([
                    'status' => $status,
                    'dipublikasi_at' => $status === 'published'
                        ? now()
                        : $anggota->dipublikasi_at,
                ]);
            }
        });

        return back()->with('success', $pesan);
    }



    /**
     * Menjadwalkan ujian.
     */

    public function schedule(Request $request, Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $validated = $request->validate([
            'waktu_mulai' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:+1 year',
            ],
            'deadline' => [
                'required',
                'date',
                'after:waktu_mulai',
                'before_or_equal:+1 year',
            ],
        ]);

        foreach (['waktu_mulai', 'deadline'] as $field) {
            $jam = Carbon::parse($validated[$field])->format('H:i');

            if ($jam < '07:00' || $jam > '17:00') {
                return back()->withErrors([
                    $field => 'Waktu harus antara pukul 07.00 sampai 17.00.',
                ])->withInput();
            }
        }

        $mulai = Carbon::parse($validated['waktu_mulai']);
        $deadline = Carbon::parse($validated['deadline']);

        if (now()->greaterThan($deadline)) {
            $status = 'completed';
        } elseif (now()->lt($mulai)) {
            $status = 'scheduled';
        } else {
            $status = 'published';
        }

        $ujian->update([
            'waktu_mulai' => $validated['waktu_mulai'],
            'deadline' => $validated['deadline'],
            'status' => $status,
            'dipublikasi_at' => in_array($status, ['published', 'completed'], true)
                ? ($ujian->dipublikasi_at ?? ($status === 'published' ? now() : null))
                : null,
        ]);

        return back()->with('success', 'Jadwal ujian berhasil diperbarui.');
    }

    public function destroy(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        // Ambil seluruh anggota grup.
        if ($ujian->id_grup_ujian) {
            $grupUjians = Ujian::where(
                'id_grup_ujian',
                $ujian->id_grup_ujian
            )->get();
        } else {
            $grupUjians = collect([$ujian]);
        }

        // Pastikan seluruh anggota grup milik guru yang login.
        foreach ($grupUjians as $anggota) {
            $this->authorizeGuruUjian($anggota);
        }

        $ujianIds = $grupUjians->pluck('id');

        // Ambil semua soal dari seluruh anggota grup.
        $soals = Soal::whereIn('ujian_id', $ujianIds)->get();

        $soalIds = $soals->pluck('id');

        // Jangan hapus jika sudah ada data pengerjaan siswa.
        $adaPengerjaan = \App\Models\SeleksiUjian::whereIn(
            'ujian_id',
            $ujianIds
        )->exists();

        // Pemeriksaan tambahan pada tabel jawaban siswa.
        if (!$adaPengerjaan && $soalIds->isNotEmpty()) {
            $adaPengerjaan = DB::table('menjawabs')
                ->whereIn('soal_id', $soalIds)
                ->exists();
        }

        if ($adaPengerjaan) {
            return back()->withErrors([
                'hapus' => 'Ujian tidak dapat dihapus karena sudah memiliki data pengerjaan siswa.',
            ]);
        }

        // Simpan lokasi gambar untuk dibersihkan setelah transaksi berhasil.
        $gambarSoal = $soals
            ->pluck('gambar')
            ->filter()
            ->unique()
            ->values();

        // Hapus seluruh soal dan ujian dalam satu transaksi.
        DB::transaction(function () use ($soals, $grupUjians) {

            foreach ($soals as $soal) {
                $soal->delete();
            }

            foreach ($grupUjians as $anggota) {
                $anggota->delete();
            }
        });

        // Hapus file gambar jika transaksi database berhasil.
        foreach ($gambarSoal as $gambar) {
            Storage::disk('public')->delete($gambar);
        }

        return redirect()
            ->route('guru.ujian.index')
            ->with('success', 'Ujian dan seluruh kelas dalam grup berhasil dihapus.');
    }



    /**
     * Memastikan ujian adalah milik guru yang sedang login.
     */
    private function authorizeGuruUjian(Ujian $ujian): void
    {
        $guruId = auth()->user()->guru_id;

        $belongsToGuru = $ujian->pengampuKelas()
            ->whereHas('guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->exists();

        abort_unless($belongsToGuru, 403);
    }
    /**
     * Normalisasi kunci jawaban pilihan menjadi huruf kecil a-f.
     * Mendukung data lama berupa indeks angka 0-5.
     */
    private function normalizeKunciPilihan($kunci): array
    {
        if (!is_array($kunci)) {
            $kunci = $kunci !== null && $kunci !== '' ? [$kunci] : [];
        }

        return collect($kunci)
            ->map(function ($value) {
                $value = strtolower(trim((string) $value));

                // Data lama: 0=a, 1=b, ..., 5=f.
                if (preg_match('/^[0-5]$/', $value)) {
                    return chr(97 + (int) $value);
                }

                return $value;
            })
            ->filter(fn($value) => preg_match('/^[a-f]$/', $value))
            ->unique()
            ->values()
            ->all();
    }


    /**
     * Menyiapkan pilihan jawaban agar bisa disimpan sebagai JSON.
     */
    private function preparePilihan($pilihan): ?array
    {
        if (empty($pilihan)) {
            return null;
        }

        if (is_string($pilihan)) {
            $decoded = json_decode($pilihan, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return array_values(
                array_filter(
                    preg_split('/\r\n|\r|\n/', $pilihan)
                )
            );
        }

        return $pilihan;
    }


    /**
     * Menyiapkan kunci jawaban.
     */
    private function prepareKunciJawaban($kunci): ?array
    {
        if (empty($kunci)) {
            return null;
        }

        if (is_string($kunci)) {
            $decoded = json_decode($kunci, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return array_values(
                array_filter(
                    preg_split('/\r\n|\r|\n/', $kunci)
                )
            );
        }

        return $kunci;
    }
}
