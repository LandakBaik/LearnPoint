<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Menjawab;
use App\Models\SeleksiUjian;
use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\Ujian;
use App\Models\Soal;
use App\Models\Notifikasi;
use App\Models\GuruMapel;
use App\Models\PengampuKelas;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


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
            ->withCount('seleksiUjians')
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

        // Pisahkan ujian yang diarsipkan ke folder khusus (ala WhatsApp)
        $ujiansArsip = $daftarUjian->filter(function ($ujian) {
            return (bool) $ujian->is_archived;
        })->values();

        // Daftar utama hanya menampilkan ujian yang TIDAK diarsipkan
        $daftarUjianAktif = $daftarUjian->reject(function ($ujian) {
            return (bool) $ujian->is_archived;
        })->values();

        // Pagination manual untuk daftar utama.
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();

        $ujians = new \Illuminate\Pagination\LengthAwarePaginator(
            $daftarUjianAktif->forPage($currentPage, $perPage)->values(),
            $daftarUjianAktif->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Statistik dihitung berdasarkan jumlah grup.
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

            'archived' => $ujiansArsip->count(),
        ];

        return view('guru.kelola_ujian.index', compact(
            'ujians',
            'ujiansArsip',
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

            'soals' => ['required', 'array', 'min:2', 'max:100'],
            'soals.*.pertanyaan' => ['required', 'string', 'max:500'],
            'soals.*.tipe_soal' => [
                'required',
                'in:single_choice,multiple_choice,essay,matching',
            ],
            'soals.*.pilihan' => ['nullable', 'array', 'max:6'],
            'soals.*.kunci_jawaban' => ['nullable'],
            'soals.*.bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'soals.*.kesulitan' => ['nullable', 'in:1,2,3'],
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
            ->sum(fn($soal) => (float) ($soal['bobot'] ?? 0));

        if (round($totalBobot, 2) > 100.01) {
            return back()
                ->withInput()
                ->withErrors([
                    'soals' => "Total bobot soal adalah {$totalBobot}. Maksimal 100 poin.",
                ]);
        }

        // Pastikan minimal ada 1 soal valid (kesulitan > 0 dan bobot > 0)
        $adaSoalValid = collect($validated['soals'])
            ->contains(fn($soal) => (int) ($soal['kesulitan'] ?? 1) > 0 && (float) ($soal['bobot'] ?? 0) > 0);

        if (!$adaSoalValid || round($totalBobot, 2) <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'soals' => 'Minimal harus ada 1 soal yang valid (memiliki bobot lebih dari 0).',
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

            // Jadwal harus berada antara pukul 07.00â€“17.00.
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

                if ($data['tipe_soal'] !== 'essay') {
                    $kunci = $this->normalizeKunciPilihan($kunci);
                }

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
                    'kesulitan' => isset($data['kesulitan']) ? (int) $data['kesulitan'] : 1,
                    'bobot' => (float) ($data['bobot'] ?? 0),
                    'urutan' => $data['urutan'] ?? ($index + 1),
                ];
            }

            // Map input sesi, token, dan jam sesi dari form create
            $sesiInput = $request->input('sesi_kelas', []);
            $tokenInput = $request->input('token_sesi', []);
            $jamSesiInput = $request->input('jam_sesi', []);
            $tokenPerSesi = [];
            $unlockCodeGrup = strtoupper(Str::random(6));

            // Buat satu ujian untuk setiap kelas
            foreach ($pengampuKelases as $pengampuKelas) {
                $nomorSesi = max(1, (int) ($sesiInput[$pengampuKelas->id] ?? 1));

                // Gunakan token dari form jika ada, jika kosong buat otomatis
                if (!isset($tokenPerSesi[$nomorSesi])) {
                    $tokenDariForm = strtoupper(trim((string) ($tokenInput[$nomorSesi] ?? '')));
                    $tokenPerSesi[$nomorSesi] = $tokenDariForm !== ''
                        ? Str::limit($tokenDariForm, 20, '')
                        : ('S' . $nomorSesi . '-' . strtoupper(Str::random(4)));
                }

                // Hitung waktu mulai sesi: pakai jam_sesi manual jika diisi, jika tidak berurutan sesuai durasi
                $waktuMulaiSesi = null;
                if ($waktuMulai) {
                    $jamManual = trim((string) ($jamSesiInput[$nomorSesi] ?? ''));
                    if ($jamManual !== '' && preg_match('/^\d{2}:\d{2}$/', $jamManual)) {
                        $waktuMulaiSesi = Carbon::parse($waktuMulai->format('Y-m-d') . ' ' . $jamManual);
                    } else {
                        $waktuMulaiSesi = $waktuMulai->copy()->addMinutes(($nomorSesi - 1) * (int) $validated['durasi_menit']);
                    }
                }

                $ujian = Ujian::create([
                    'id_grup_ujian' => $grupUjianId,
                    'judul' => $validated['judul'],
                    'kisi_kisi' => $validated['kisi_kisi'] ?? null,
                    'pengampu_kelas_id' => $pengampuKelas->id,
                    'sesi' => $nomorSesi,
                    'durasi_menit' => $validated['durasi_menit'],
                    'kkm' => $validated['kkm'],
                    'tampilkan_nilai' => $request->boolean('tampilkan_nilai'),
                    'waktu_mulai' => $waktuMulai,
                    'waktu_mulai_sesi' => $waktuMulaiSesi,
                    'deadline' => $deadlineUjian,
                    'status' => $status,
                    'token' => $tokenPerSesi[$nomorSesi],
                    'unlock_code' => $unlockCodeGrup,
                    'dipublikasi_at' => $action === 'publish' ? now() : null,
                ]);

                // Salin soal ke ujian milik kelas ini
                foreach ($dataSoals as $dataSoal) {
                    Soal::create([
                        'pertanyaan' => $dataSoal['pertanyaan'],
                        'tipe_soal' => $dataSoal['tipe_soal'],
                        'pilihan' => $dataSoal['pilihan'],
                        'kunci_jawaban' => $dataSoal['kunci_jawaban'],
                        'gambar' => $dataSoal['gambar'],
                        'kesulitan' => max(1, (int) $dataSoal['kesulitan']),
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

        $ujian->sinkronisasiStatus();

        $ujian->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
            'seleksiUjians',
        ]);

        // Ambil seluruh anggota kelas dalam grup ujian yang sama
        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with([
                'pengampuKelas.guruMapel.mapel',
                'pengampuKelas.kelas',
                'seleksiUjians',
            ])
                ->where('id_grup_ujian', $ujian->id_grup_ujian)
                ->get()
            : collect([$ujian]);

        return view('guru.kelola_ujian.show', compact('ujian', 'grupUjians'));
    }


    /**
     * Menampilkan halaman edit (Mode Penuh untuk draft/scheduled, Mode Edit Bobot untuk unpublished/completed).
     */
    public function edit(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $ujian->sinkronisasiStatus();

        if (!$ujian->bolehDiedit()) {
            return redirect()->route('guru.ujian.index')->with(
                'error',
                'Ujian sedang berlangsung (Live). Silakan tarik publikasi terlebih dahulu jika ingin menyesuaikan bobot soal.'
            );
        }

        $guruId = auth()->user()->guru_id;
        $modeEditBobot = $ujian->hanyaBisaEditBobot();

        $queryGrup = Ujian::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
        ])->whereHas('pengampuKelas.guruMapel', function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        });

        if ($ujian->id_grup_ujian) {
            $grupUjians = $queryGrup
                ->where('id_grup_ujian', $ujian->id_grup_ujian)
                ->get();
        } else {
            $grupUjians = $queryGrup
                ->whereKey($ujian->id)
                ->get();
        }

        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->get();

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

        $ujian->load(['soals' => fn($q) => $q->orderBy('urutan')->orderBy('id')]);

        // Pertahankan nilai kesulitan = 0 jika bobot soal adalah 0
        $soalData = $ujian->soals->map(function ($soal) {
            $bobotSoal = (float) ($soal->bobot ?? 0);
            $kesulitanSoal = !is_null($soal->kesulitan)
                ? (int) $soal->kesulitan
                : ($bobotSoal <= 0 ? 0 : 1);

            if ($bobotSoal <= 0) {
                $kesulitanSoal = 0;
            }

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
                'kesulitan' => $kesulitanSoal,
                'bobot' => $bobotSoal,
            ];
        })->values()->all();

        $modeJadwalkan = request()->query('aksi') === 'jadwalkan';

        // Siapkan data sesi, token, dan jam sesi awal untuk form edit
        $sesiKelasAwal = $grupUjians
            ->mapWithKeys(fn($item) => [(string) $item->pengampu_kelas_id => (int) ($item->sesi ?: 1)])
            ->all();

        $tokenSesiAwal = $grupUjians
            ->groupBy(fn($item) => (int) ($item->sesi ?: 1))
            ->map(fn($items) => $items->pluck('token')->filter()->first())
            ->filter()
            ->all();

        $jamSesiAwal = $grupUjians
            ->groupBy(fn($item) => (int) ($item->sesi ?: 1))
            ->map(function ($items) {
                $mulai = $items->first()?->waktu_mulai_sesi;
                return $mulai ? Carbon::parse($mulai)->format('H:i') : null;
            })
            ->filter()
            ->all();

        $modeJadwalkan = request()->query('aksi') === 'jadwalkan';

        return view('guru.kelola_ujian.edit', compact(
            'ujian',
            'pengampuKelases',
            'grupUjians',
            'soalData',
            'modeJadwalkan',
            'dataMapel',
            'modeEditBobot',
            'sesiKelasAwal',
            'tokenSesiAwal',
            'jamSesiAwal'
        ));
    }

    /**
     * Update ujian beserta butir soal:
     * - Jika status `unpublished` atau `completed`: HANYA memperbarui kesulitan & bobot soal TANPA menghapus
     *   baris soal (agar riwayat jawaban siswa Sesi 1 aman), mempertahankan status ujian, dan menghitung ulang nilai siswa.
     * - Jika status `draft` atau `scheduled`: Memperbarui seluruh data ujian & soal.
     */
    public function update(Request $request, Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $ujian->sinkronisasiStatus();

        if (!$ujian->bolehDiedit()) {
            return redirect()->route('guru.ujian.index')->with(
                'error',
                'Ujian sedang berlangsung (Live). Silakan tarik publikasi terlebih dahulu sebelum menyimpan perubahan.'
            );
        }

        $grup = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        if ($grup->isEmpty()) {
            $grup = collect([$ujian]);
        }

        // =====================================================================
        // CABANG 1: MODE EDIT KHUSUS BOBOT (STATUS UNPUBLISHED / COMPLETED)
        // =====================================================================
        if ($ujian->hanyaBisaEditBobot()) {
            $validatedBobot = $request->validate([
                'soals' => ['required', 'array', 'min:1'],
                'soals.*.kesulitan' => ['required', 'in:1,2,3'],
                'soals.*.bobot' => ['required', 'numeric', 'min:0', 'max:100'],
                'soals.*.urutan' => ['nullable', 'integer', 'min:1'],
            ]);

            $adaSoalValid = collect($validatedBobot['soals'])
                ->contains(fn($s) => (int) ($s['kesulitan'] ?? 0) > 0 && (float) ($s['bobot'] ?? 0) > 0);

            if (!$adaSoalValid) {
                return back()->withInput()->withErrors([
                    'soals' => 'Minimal harus ada 1 soal yang valid (memiliki bobot lebih dari 0).',
                ]);
            }

            $totalBobot = collect($validatedBobot['soals'])->sum(fn($s) => (float) ($s['bobot'] ?? 0));
            if (round($totalBobot, 2) > 100.01 || round($totalBobot, 2) < 99.9) {
                return back()->withInput()->withErrors([
                    'soals' => "Total bobot soal harus genap 100 poin (saat ini: {$totalBobot}).",
                ]);
            }

            DB::transaction(function () use ($validatedBobot, $grup) {
                foreach ($grup as $anggota) {
                    // Tambahkan ->get() sebelum ->values() agar menjadi Collection
                    $soalsKelas = $anggota->soals()
                        ->orderBy('urutan')
                        ->orderBy('id')
                        ->get()
                        ->values();

                    foreach (array_values($validatedBobot['soals']) as $idx => $inputSoal) {
                        $urutanTarget = (int) ($inputSoal['urutan'] ?? ($idx + 1));

                        $soalTarget = $soalsKelas->firstWhere('urutan', $urutanTarget)
                            ?? $soalsKelas->get($idx);

                        if ($soalTarget) {
                            $kesulitanBaru = (int) $inputSoal['kesulitan'];
                            $bobotBaru = $kesulitanBaru === 0 ? 0 : (float) $inputSoal['bobot'];

                            $soalTarget->update([
                                'kesulitan' => $kesulitanBaru,
                                'bobot'     => $bobotBaru,
                            ]);
                        }
                    }

                    // Rekalkulasi otomatis seluruh siswa yang sudah menyelesaikan ujian di kelas ini
                    $sesiSelesaiList = SeleksiUjian::where('ujian_id', $anggota->id)
                        ->where(function ($q) {
                            $q->where('status', 'selesai')->orWhereNotNull('waktu_selesai');
                        })
                        ->get();

                    foreach ($sesiSelesaiList as $sesiSiswa) {
                        $this->rekalkulasiNilaiSiswa($anggota->id, $sesiSiswa->siswa_id);
                    }
                }
            });

            return redirect()
                ->route('guru.ujian.index')
                ->with(
                    'success',
                    'Bobot soal berhasil diperbarui dan nilai siswa sesi sebelumnya telah dihitung ulang otomatis tanpa menghapus riwayat pengerjaan.'
                );
        }

        // =====================================================================
        // CABANG 2: MODE EDIT PENUH (STATUS DRAFT / SCHEDULED)
        // =====================================================================
        $modeJadwalkan = $request->query('aksi') === 'jadwalkan';

        $validated = $request->validate([
            'mapel_id' => ['nullable', 'integer'],
            'pengampu_kelas_id' => ['required'],
            'pengampu_kelas_id.*' => ['nullable', 'integer'],

            'judul' => ['required', 'string', 'max:100', 'regex:/^[\pL\pN ]+$/u'],
            'kisi_kisi' => ['nullable', 'string', 'max:500'],
            'durasi_menit' => ['required', 'integer', 'min:1', 'max:999'],
            'kkm' => ['required', 'numeric', 'min:0', 'max:100'],

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

            'soals' => ['required', 'array', 'min:2', 'max:100'],
            'soals.*.id' => ['nullable', 'integer'],
            'soals.*.pertanyaan' => ['required', 'string', 'max:500'],
            'soals.*.tipe_soal' => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'soals.*.pilihan' => ['nullable', 'array', 'max:6'],
            'soals.*.kunci_jawaban' => ['nullable'],
            'soals.*.kunci_jawaban_text' => ['nullable', 'string'],
            'soals.*.kesulitan' => ['nullable', 'in:0,1,2,3'],
            'soals.*.bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'soals.*.urutan' => ['nullable', 'integer', 'min:1'],
            'soals.*.gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ]);

        $adaSoalValid = collect($validated['soals'])
            ->contains(fn($s) => (int) ($s['kesulitan'] ?? 1) > 0 && (float) ($s['bobot'] ?? 0) > 0);

        if (!$adaSoalValid) {
            return back()->withInput()->withErrors([
                'soals' => 'Minimal harus ada 1 soal yang valid (bukan Tidak Valid).',
            ]);
        }

        $totalBobot = collect($validated['soals'])->sum(fn($s) => (float) ($s['bobot'] ?? 0));
        if (round($totalBobot, 2) > 100.01) {
            return back()->withInput()->withErrors([
                'soals' => "Total bobot soal adalah {$totalBobot}. Maksimal 100 poin.",
            ]);
        }

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

        $kelasIds = is_array($request->pengampu_kelas_id)
            ? array_map('intval', $request->pengampu_kelas_id)
            : [(int) $request->pengampu_kelas_id];

        DB::transaction(function () use ($request, $validated, $grup, $kelasIds, $status, $ujian) {
            $grupUjianId = $ujian->id_grup_ujian ?: (string) Str::uuid();
            $soalSumber = $ujian->soals()->get()->keyBy('urutan');
            $dataSoals = [];

            foreach ($request->input('soals', []) as $index => $data) {
                $urutan = (int) ($data['urutan'] ?? ($index + 1));
                $soalLama = $soalSumber->get($urutan);
                $gambar = $soalLama?->gambar;

                if (($data['hapus_gambar'] ?? '0') === '1') {
                    $gambar = null;
                }

                if ($request->hasFile("soals.$index.gambar")) {
                    $gambar = $request->file("soals.$index.gambar")->store('soal-images', 'public');
                }

                $pilihan = is_array($data['pilihan'] ?? null)
                    ? array_values(array_filter($data['pilihan'], fn($v) => $v !== null && trim((string) $v) !== ''))
                    : [];

                $kunciJawaban = $data['kunci_jawaban'] ?? [];
                if (!is_array($kunciJawaban)) {
                    $kunciJawaban = [$kunciJawaban];
                }
                $kunciJawaban = array_values(array_filter($kunciJawaban, fn($v) => $v !== null && trim((string) $v) !== ''));

                if (in_array($data['tipe_soal'], ['single_choice', 'multiple_choice'], true)) {
                    $kunciJawaban = $this->normalizeKunciPilihan($kunciJawaban);
                }

                if ($data['tipe_soal'] === 'essay') {
                    $jawabanEssay = trim((string) ($data['kunci_jawaban_text'] ?? ($data['kunci_jawaban'][0] ?? $data['kunci_jawaban'] ?? '')));
                    $kunciJawaban = $jawabanEssay !== '' ? [$jawabanEssay] : [];
                }

                $kesulitan = isset($data['kesulitan']) ? (int) $data['kesulitan'] : 1;
                $bobot = $kesulitan === 0 ? 0 : (float) ($data['bobot'] ?? 0);

                $dataSoals[] = [
                    'pertanyaan' => $data['pertanyaan'],
                    'tipe_soal' => $data['tipe_soal'],
                    'pilihan' => $pilihan,
                    'kunci_jawaban' => $kunciJawaban,
                    'gambar' => $gambar,
                    'kesulitan' => $kesulitan,
                    'bobot' => $bobot,
                    'urutan' => $urutan,
                    'quiz_id' => null,
                    'tugas_id' => null,
                ];
            }

            $existingKelasIds = $grup->pluck('pengampu_kelas_id')->toArray();

            // Ambil pengaturan sesi, token, dan jam sesi dari form edit
            $sesiInput = $request->input('sesi_kelas', []);
            $tokenInput = $request->input('token_sesi', []);
            $jamSesiInput = $request->input('jam_sesi', []);
            $tokenPerSesi = [];
            $unlockCodeGrup = $ujian->unlock_code ?: strtoupper(Str::random(6));
            $waktuMulaiCarbon = !empty($validated['waktu_mulai']) ? Carbon::parse($validated['waktu_mulai']) : null;

            $hitungSesiDanToken = function (int $pengampuId, ?Ujian $existingUjian = null) use (
                $sesiInput,
                $tokenInput,
                $jamSesiInput,
                &$tokenPerSesi,
                $waktuMulaiCarbon,
                $validated
            ) {
                $nomorSesi = max(1, (int) ($sesiInput[$pengampuId] ?? ($existingUjian?->sesi ?? 1)));

                if (!isset($tokenPerSesi[$nomorSesi])) {
                    $tokenForm = strtoupper(trim((string) ($tokenInput[$nomorSesi] ?? '')));
                    $tokenLama = ($existingUjian && (int) $existingUjian->sesi === $nomorSesi) ? $existingUjian->token : null;
                    $tokenPerSesi[$nomorSesi] = $tokenForm !== ''
                        ? Str::limit($tokenForm, 20, '')
                        : ($tokenLama ?: ('S' . $nomorSesi . '-' . strtoupper(Str::random(4))));
                }

                $waktuMulaiSesi = null;
                if ($waktuMulaiCarbon) {
                    $jamManual = trim((string) ($jamSesiInput[$nomorSesi] ?? ''));
                    if ($jamManual !== '' && preg_match('/^\d{2}:\d{2}$/', $jamManual)) {
                        $waktuMulaiSesi = Carbon::parse($waktuMulaiCarbon->format('Y-m-d') . ' ' . $jamManual);
                    } else {
                        $waktuMulaiSesi = $waktuMulaiCarbon->copy()->addMinutes(($nomorSesi - 1) * (int) $validated['durasi_menit']);
                    }
                }

                return [
                    'sesi' => $nomorSesi,
                    'token' => $tokenPerSesi[$nomorSesi],
                    'waktu_mulai_sesi' => $waktuMulaiSesi,
                ];
            };

            foreach ($grup as $anggota) {
                if (in_array($anggota->pengampu_kelas_id, $kelasIds)) {
                    $infoSesi = $hitungSesiDanToken((int) $anggota->pengampu_kelas_id, $anggota);

                    $anggota->update([
                        'id_grup_ujian' => $grupUjianId,
                        'judul' => $validated['judul'],
                        'kisi_kisi' => $validated['kisi_kisi'] ?? null,
                        'sesi' => $infoSesi['sesi'],
                        'token' => $infoSesi['token'],
                        'unlock_code' => $anggota->unlock_code ?: $unlockCodeGrup,
                        'durasi_menit' => $validated['durasi_menit'],
                        'kkm' => (float) $validated['kkm'],
                        'waktu_mulai' => $validated['waktu_mulai'] ?? null,
                        'waktu_mulai_sesi' => $infoSesi['waktu_mulai_sesi'],
                        'tampilkan_nilai' => $request->boolean('tampilkan_nilai'),
                        'deadline' => $validated['deadline'] ?? null,
                        'status' => $status,
                        'dipublikasi_at' => $status === 'published'
                            ? ($anggota->dipublikasi_at ?? now())
                            : ($status === 'completed' ? $anggota->dipublikasi_at : null),
                    ]);

                    // Update in-place berdasarkan urutan agar ID soal tetap stabil
                    $existingSoals = $anggota->soals()->orderBy('urutan')->orderBy('id')->get()->values();
                    $usedSoalIds = [];

                    foreach ($dataSoals as $idx => $soalItem) {
                        $soalItem['kesulitan'] = max(1, (int) $soalItem['kesulitan']);
                        $existing = $existingSoals->get($idx);
                        if ($existing) {
                            $existing->update($soalItem);
                            $usedSoalIds[] = $existing->id;
                        } else {
                            $created = $anggota->soals()->create($soalItem);
                            $usedSoalIds[] = $created->id;
                        }
                    }

                    $anggota->soals()->whereNotIn('id', $usedSoalIds)->delete();
                } else {
                    $anggota->soals()->delete();
                    $anggota->delete();
                }
            }

            $newKelasIds = array_diff($kelasIds, $existingKelasIds);
            foreach ($newKelasIds as $newPengampuId) {
                $infoSesi = $hitungSesiDanToken((int) $newPengampuId, null);

                $baru = Ujian::create([
                    'id_grup_ujian' => $grupUjianId,
                    'judul' => $validated['judul'],
                    'kisi_kisi' => $validated['kisi_kisi'] ?? null,
                    'pengampu_kelas_id' => $newPengampuId,
                    'sesi' => $infoSesi['sesi'],
                    'token' => $infoSesi['token'],
                    'unlock_code' => $unlockCodeGrup,
                    'durasi_menit' => $validated['durasi_menit'],
                    'kkm' => (float) $validated['kkm'],
                    'tampilkan_nilai' => $request->boolean('tampilkan_nilai'),
                    'waktu_mulai' => $validated['waktu_mulai'] ?? null,
                    'waktu_mulai_sesi' => $infoSesi['waktu_mulai_sesi'],
                    'deadline' => $validated['deadline'] ?? null,
                    'status' => $status,
                    'dipublikasi_at' => $status === 'published' ? now() : null,
                ]);

                foreach ($dataSoals as $soalItem) {
                    $soalItem['kesulitan'] = max(1, (int) $soalItem['kesulitan']);
                    $baru->soals()->create($soalItem);
                }
            }
        });

        return redirect()
            ->route('guru.ujian.show', $ujian)
            ->with('success', 'Ujian berhasil diperbarui.');
    }
    /**
     * Publikasikan ujian: langsung live atau set jadwal (mendukung grup kelas).
     */
    public function publish(Request $request, Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $request->validate([
            'tipe_publikasi' => 'required|in:langsung,jadwal',
            'waktu_mulai' => 'nullable|required_if:tipe_publikasi,jadwal|date',
            'deadline' => 'nullable|date|after:waktu_mulai',
        ]);

        // Ambil semua kelas dalam grup ujian yang sama
        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        foreach ($grupUjians as $anggota) {
            $this->authorizeGuruUjian($anggota);
        }

        if ($request->tipe_publikasi === 'jadwal') {
            $waktuMulai = Carbon::parse($request->waktu_mulai);
            $deadline = $request->deadline ? Carbon::parse($request->deadline) : $ujian->deadline;

            // Jika waktu mulai sudah lewat, langsung jadikan live
            if ($waktuMulai->isPast()) {
                DB::transaction(function () use ($grupUjians, $waktuMulai, $deadline) {
                    foreach ($grupUjians as $anggota) {
                        $anggota->update([
                            'status' => 'published',
                            'waktu_mulai' => $waktuMulai,
                            'deadline' => $deadline,
                            'dipublikasi_at' => now(),
                        ]);
                    }
                });

                return back()->with('success', 'Waktu mulai telah tiba. Ujian langsung dipublikasikan (Live) untuk semua kelas.');
            }

            // 1. Bagian Ujian Dijadwalkan (Scheduled)
            $waktuMulai = Carbon::parse($waktuMulai); // Pastikan berformat Carbon

            DB::transaction(function () use ($grupUjians, $waktuMulai, $deadline) {
                $tokenPerSesi = [];

                foreach ($grupUjians as $anggota) {
                    $sesi = (int) ($anggota->sesi ?: 1);

                    if (!isset($tokenPerSesi[$sesi])) {
                        $tokenPerSesi[$sesi] = $anggota->token ?: ('S' . $sesi . '-' . strtoupper(Str::random(4)));
                    }

                    // Perbaikan rumus: ($sesi - 1) dikali durasi_menit
                    $mulaiSesi = $waktuMulai->copy()->addMinutes(($sesi - 1) * (int) $anggota->durasi_menit);

                    $anggota->update([
                        'status' => 'scheduled',
                        'waktu_mulai' => $waktuMulai,
                        'waktu_mulai_sesi' => $mulaiSesi,
                        'deadline' => $deadline,
                        'token' => $tokenPerSesi[$sesi],
                        'dipublikasi_at' => null,
                    ]);
                }
            });

            return back()->with('success', 'Ujian berhasil dijadwalkan dan akan aktif otomatis pada jadwal.');
        }

        // 2. Bagian Publikasi Langsung (Live / Published)
        $waktuMulai = $ujian->waktu_mulai ? Carbon::parse($ujian->waktu_mulai) : now();
        $deadline = $request->deadline ? Carbon::parse($request->deadline) : $ujian->deadline;

        DB::transaction(function () use ($grupUjians, $waktuMulai, $deadline) {
            $tokenPerSesi = [];

            foreach ($grupUjians as $anggota) {
                $sesi = (int) ($anggota->sesi ?: 1);

                if (!isset($tokenPerSesi[$sesi])) {
                    $tokenPerSesi[$sesi] = $anggota->token ?: ('S' . $sesi . '-' . strtoupper(Str::random(4)));
                }

                $mulaiSesi = $waktuMulai->copy()->addMinutes(($sesi - 1) * (int) $anggota->durasi_menit);

                $anggota->update([
                    'status' => 'published',
                    'waktu_mulai' => $waktuMulai,
                    'waktu_mulai_sesi' => $mulaiSesi,
                    'deadline' => $deadline,
                    'token' => $tokenPerSesi[$sesi],
                    'dipublikasi_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Ujian berhasil dipublikasikan dan sekarang dapat dikerjakan siswa.');
    }

    /**
     * Menarik publikasi ujian (Rem Darurat).
     * Nilai dan rekaman pengerjaan siswa yang sudah selesai tetap tersimpan utuh.
     */
    public function unpublish(Request $request, Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        foreach ($grupUjians as $anggota) {
            $this->authorizeGuruUjian($anggota);
        }

        // Langsung ubah status menjadi unpublished tanpa memblokir data siswa
        DB::transaction(function () use ($grupUjians) {
            foreach ($grupUjians as $anggota) {
                $anggota->update([
                    'status' => 'unpublished',
                ]);
            }
        });

        return back()->with('success', 'Ujian berhasil ditarik dari publikasi. Nilai sesi sebelumnya tetap tersimpan di rekap.');
    }

    /**
     * Menggandakan (Clone) seluruh instrumen soal ke ujian draf baru tanpa menyertakan riwayat siswa.
     */
    public function duplicate(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        foreach ($grupUjians as $anggota) {
            $this->authorizeGuruUjian($anggota);
        }

        return DB::transaction(function () use ($grupUjians) {
            $grupBaruId = (string) Str::uuid();

            foreach ($grupUjians as $anggota) {
                $ujianBaru = $anggota->replicate([
                    'waktu_mulai',
                    'deadline',
                    'dipublikasi_at',
                    'created_at',
                    'updated_at',
                ]);

                $ujianBaru->judul = $anggota->judul . ' (Salinan)';
                $ujianBaru->status = 'draft';
                $ujianBaru->id_grup_ujian = $grupBaruId;
                $ujianBaru->waktu_mulai = null;
                $ujianBaru->deadline = null;
                $ujianBaru->dipublikasi_at = null;
                $ujianBaru->save();

                foreach ($anggota->soals as $soal) {
                    $soalBaru = $soal->replicate([
                        'created_at',
                        'updated_at',
                    ]);
                    $soalBaru->ujian_id = $ujianBaru->id;
                    $soalBaru->save();
                }
            }

            return redirect()->route('guru.ujian.index')
                ->with('success', 'Instrumen ujian berhasil diduplikasi menjadi draf baru tanpa menyertakan data siswa.');
        });
    }

    /**
     * Memindahkan ujian selesai ke dalam Folder Arsip atau mengeluarkannya kembali (ala WhatsApp).
     */
    public function toggleArchive(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        $statusArsipBaru = !$ujian->is_archived;

        DB::transaction(function () use ($grupUjians, $statusArsipBaru) {
            foreach ($grupUjians as $anggota) {
                $anggota->update([
                    'is_archived' => $statusArsipBaru,
                ]);
            }
        });

        $pesan = $statusArsipBaru
            ? 'Ujian berhasil dipindahkan ke folder Diarsipkan.'
            : 'Ujian berhasil dikeluarkan dari folder Diarsipkan.';

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
     * Menampilkan halaman view monitoring ujian live beserta manajemen sesi & token.
     */
    public function monitoring(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $ujian->sinkronisasiStatus();
        $isSelesai = $ujian->status === 'completed';

        $ujian->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
        ]);

        // Ambil seluruh kelas dalam grup ujian yang sama
        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with('pengampuKelas.kelas')->where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        // Pastikan setiap sesi sudah memiliki token
        $tokenPerSesi = [];
        foreach ($grupUjians as $item) {
            $s = (int) ($item->sesi ?: 1);
            if (!isset($tokenPerSesi[$s])) {
                $tokenPerSesi[$s] = $item->token ?: ('S' . $s . '-' . strtoupper(Str::random(4)));
            }
            if (!$item->token || $item->token !== $tokenPerSesi[$s]) {
                $item->update([
                    'sesi' => $s,
                    'token' => $tokenPerSesi[$s],
                    'waktu_mulai_sesi' => $item->waktu_mulai_sesi ?? $item->waktu_mulai ?? now(),
                ]);
            }
        }

        // Susun ringkasan per Sesi untuk ditampilkan di UI Monitoring
        $daftarSesi = $grupUjians->groupBy('sesi')->sortKeys()->map(function ($items, $nomorSesi) {
            $first = $items->first();
            $mulai = $first->waktu_mulai_sesi ?? $first->waktu_mulai ?? now();
            $selesai = Carbon::parse($mulai)->addMinutes((int) $first->durasi_menit);

            return [
                'sesi' => (int) $nomorSesi,
                'token' => $first->token,
                'kelas_list' => $items->map(fn($u) => $u->pengampuKelas?->kelas?->nama_kelas ?? '-')->values()->all(),
                'jam_mulai' => Carbon::parse($mulai)->format('H:i'),
                'jam_selesai' => $selesai->format('H:i'),
            ];
        })->values();

        // Pastikan grup ujian memiliki unlock_code aktif
        $unlockCode = $ujian->unlock_code;
        if (empty($unlockCode)) {
            $unlockCode = strtoupper(Str::random(6));
            if ($ujian->id_grup_ujian) {
                Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->update(['unlock_code' => $unlockCode]);
            } else {
                $ujian->update(['unlock_code' => $unlockCode]);
            }
        }

        $totalSoal = $ujian->soals->count();

        return view('guru.kelola_ujian.monitoring', compact('ujian', 'totalSoal', 'grupUjians', 'daftarSesi', 'unlockCode', 'isSelesai'));
    }

    /**
     * Endpoint JSON untuk polling real-time data progres & anti-cheat siswa.
     */
    public function monitoringData(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with('pengampuKelas.kelas')->where('id_grup_ujian', $ujian->id_grup_ujian)->get()->keyBy('id')
            : collect([$ujian->id => $ujian]);

        $ujianIds = $grupUjians->keys();
        $totalSoal = $ujian->soals()->count();
        $soalIds = Soal::whereIn('ujian_id', $ujianIds)->pluck('id');

        $sesiSiswa = SeleksiUjian::with(['siswa.user'])
            ->whereIn('ujian_id', $ujianIds)
            ->get();

        $siswaIds = $sesiSiswa->pluck('siswa_id')->filter()->unique();

        $jawabanCounts = Menjawab::whereIn('soal_id', $soalIds)
            ->whereIn('siswa_id', $siswaIds)
            ->select('siswa_id', DB::raw('count(distinct soal_id) as total'))
            ->groupBy('siswa_id')
            ->pluck('total', 'siswa_id');

        $pesertaList = $sesiSiswa->map(function ($sesi) use ($totalSoal, $jawabanCounts, $grupUjians) {
            $ujianSiswa = $grupUjians->get($sesi->ujian_id);
            $siswa = $sesi->siswa;
            $user = $siswa?->user;

            $nama = $user->name ?? $siswa?->nama_lengkap ?? $siswa?->nama ?? ('Siswa #' . $sesi->siswa_id);
            $nisn = $siswa?->nis ?? $siswa?->nisn ?? $user?->username ?? '-';
            $namaKelas = $ujianSiswa?->pengampuKelas?->kelas?->nama_kelas ?? '-';
            $nomorSesi = $ujianSiswa?->sesi ?? 1;

            $totalDijawab = $jawabanCounts[$sesi->siswa_id] ?? 0;
            $progresPersen = $totalSoal > 0 ? round(($totalDijawab / $totalSoal) * 100) : 0;

            $sudahSelesai = ($sesi->status === 'selesai') || !is_null($sesi->waktu_selesai);

            // Hitung sisa waktu berdasarkan batas akhir sesi token
            $sisaDetik = 0;
            if (!$sudahSelesai) {
                $durasiMenit = (int) ($ujianSiswa?->durasi_menit ?? 60);
                $waktuAwalSesi = $ujianSiswa?->waktu_mulai_sesi ?? $ujianSiswa?->waktu_mulai ?? $sesi->waktu_mulai ?? now();
                $batasAkhirSesi = Carbon::parse($waktuAwalSesi)->addMinutes($durasiMenit);
                $sisaDetik = max(0, (int) now()->diffInSeconds($batasAkhirSesi, false));
            }

            if ($sudahSelesai) {
                $statusKey = 'selesai';
                $statusLabel = 'Selesai';
            } elseif ($sesi->status === 'terkunci') {
                $statusKey = 'terkunci';
                $statusLabel = 'Terkunci';
            } else {
                $statusKey = 'sedang_mengerjakan';
                $statusLabel = $sisaDetik <= 0 ? 'Waktu Habis' : 'Mengerjakan';
            }

            return [
                'seleksi_id' => $sesi->id,
                'ujian_id' => $sesi->ujian_id,
                'siswa_id' => $sesi->siswa_id,
                'nama' => $nama,
                'nisn' => $nisn,
                'kelas' => $namaKelas,
                'sesi' => $nomorSesi,
                'status' => $statusKey,
                'status_label' => $statusLabel,
                'total_dijawab' => $totalDijawab,
                'progres_persen' => $progresPersen,
                'sisa_detik' => (int) floor($sisaDetik),
                'total_pelanggaran' => (int) ($sesi->pelanggaran_count ?? 0),
                'nilai_akhir' => $sudahSelesai ? round((float) $sesi->nilai_akhir, 2) : null,
                'waktu_selesai' => $sesi->waktu_selesai ? Carbon::parse($sesi->waktu_selesai)->format('d M Y, H:i') : null,
            ];
        });

        $stats = [
            'total_peserta' => $pesertaList->count(),
            'aktif' => $pesertaList->where('status', 'sedang_mengerjakan')->where('sisa_detik', '>', 0)->count(),
            'selesai' => $pesertaList->where('status', 'selesai')->count(),
            'terkunci' => $pesertaList->where('status', 'terkunci')->count(),
            'pelanggaran' => $pesertaList->where('total_pelanggaran', '>', 0)->count(),
            'total_kejadian_pelanggaran' => (int) $pesertaList->sum('total_pelanggaran'),
        ];

        return response()->json([
            'stats' => $stats,
            'pesertas' => $pesertaList->values(),
        ]);
    }

    /**
     * Membuka kunci sesi siswa yang terblokir browser/perangkat
     * TANPA mereset total pelanggaran_count.
     */
    public function unlockPeserta(Ujian $ujian, SeleksiUjian $seleksi)
    {
        $this->authorizeGuruUjian($ujian);

        $seleksi->update([
            'status' => 'sedang_mengerjakan',
        ]);

        return response()->json([
            'status'            => 'success',
            'pelanggaran_count' => (int) ($seleksi->pelanggaran_count ?? 0),
            'message'           => 'Sesi siswa berhasil dibuka kembali. Riwayat pelanggaran tetap tercatat.',
        ]);
    }

    /**
     * Tarik paksa ujian siswa dari panel monitoring guru.
     */
    public function forceSubmit(Ujian $ujian, $seleksi)
    {
        $this->authorizeGuruUjian($ujian);

        // Ambil data sesi (baik jika terkirim sebagai ID maupun Model Binding)
        $seleksi = $seleksi instanceof SeleksiUjian ? $seleksi : SeleksiUjian::findOrFail($seleksi);

        if ($seleksi->status === 'selesai') {
            return response()->json(['status' => 'success', 'message' => 'Ujian siswa sudah selesai.'], 200);
        }

        return DB::transaction(function () use ($ujian, $seleksi) {
            $siswaId = $seleksi->siswa_id;
            $soals = $ujian->soals;
            $soalIds = $soals->pluck('id');

            $jawabanSiswa = Menjawab::where('siswa_id', $siswaId)
                ->whereIn('soal_id', $soalIds)
                ->get()
                ->keyBy('soal_id');

            $totalBobot = $soals->sum('bobot') ?: 100;
            $skorDiperoleh = 0;

            foreach ($soals as $soal) {
                $jawab = $jawabanSiswa->get($soal->id);
                $bobotSoal = (float) ($soal->bobot ?? 1);

                if (!$jawab || empty($jawab->jawaban_dipilih)) {
                    continue;
                }

                $kunci = $soal->kunci_jawaban;

                if ($soal->tipe_soal === 'multiple_choice') {
                    $cleanKunci = is_array($kunci)
                        ? array_map('strtolower', array_map('trim', $kunci))
                        : array_filter(array_map('strtolower', array_map('trim', explode(',', (string) $kunci))));

                    $jawabanArr = array_filter(array_map('strtolower', array_map('trim', explode(',', (string) $jawab->jawaban_dipilih))));
                    $totalKunci = count($cleanKunci);

                    if ($totalKunci > 0) {
                        $benar = count(array_intersect($jawabanArr, $cleanKunci));
                        $salah = count(array_diff($jawabanArr, $cleanKunci));
                        $net = max(0, $benar - $salah);
                        $skorDiperoleh += ($bobotSoal * ($net / $totalKunci));
                    }
                } elseif ($soal->tipe_soal === 'single_choice') {
                    $kunciTunggal = is_array($kunci) ? ($kunci[0] ?? '') : (string) $kunci;
                    if (strtolower(trim((string) $jawab->jawaban_dipilih)) === strtolower(trim((string) $kunciTunggal))) {
                        $skorDiperoleh += $bobotSoal;
                    }
                }
            }

            $nilaiAkhir = $totalBobot > 0 ? round(($skorDiperoleh / $totalBobot) * 100, 2) : 0;
            if ($nilaiAkhir > 100)
                $nilaiAkhir = 100;

            $waktuAwal = $seleksi->waktu_mulai ?? $seleksi->created_at ?? now();
            $durasiDetik = (int) Carbon::parse($waktuAwal)->diffInSeconds(now());

            // 1. Simpan perubahan ke tabel seleksi_ujians
            $seleksi->update([
                'status' => 'selesai',
                'waktu_selesai' => now(),
                'nilai_awal' => $nilaiAkhir,
                'nilai_akhir' => $nilaiAkhir,
            ]);

            // 2. Simpan nilai ke tabel nilais
            Nilai::updateOrCreate(
                [
                    'seleksi_ujian_id' => $seleksi->id,
                    'siswa_id' => $siswaId,
                ],
                [
                    'nilai' => $nilaiAkhir,
                    'status' => 'selesai',
                    'durasi' => $durasiDetik,
                    'tugas_id' => null,
                    'quiz_id' => null,
                    'file_jawaban' => null,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Ujian siswa berhasil ditarik paksa dan dinilai.'
            ]);
        });
    }

    /**
     * Membuka kembali ujian siswa yang sudah berstatus selesai (kepencet submit / keluar / shutdown)
     * DENGAN TETAP MEMPERTAHANKAN seluruh jawaban yang sudah dikerjakan di tabel menjawabs.
     */
    public function reopenPeserta(Ujian $ujian, SeleksiUjian $seleksi)
    {
        $this->authorizeGuruUjian($ujian);

        // Pastikan sesi milik ujian atau grup ujian yang sama
        $ujianMilikSiswa = Ujian::findOrFail($seleksi->ujian_id);
        if ($ujian->id_grup_ujian) {
            abort_unless($ujianMilikSiswa->id_grup_ujian === $ujian->id_grup_ujian, 403);
        } else {
            abort_unless($ujianMilikSiswa->id === $ujian->id, 403);
        }

        // Cek apakah sisa waktu sesi untuk kelas siswa tersebut masih tersedia
        $waktuMulaiSesi = Carbon::parse($ujianMilikSiswa->waktu_mulai_sesi ?? $ujianMilikSiswa->waktu_mulai ?? now());
        $batasAkhirSesi = $waktuMulaiSesi->copy()->addMinutes((int) $ujianMilikSiswa->durasi_menit);
        $sisaDetik = (int) now()->diffInSeconds($batasAkhirSesi, false);

        if ($sisaDetik <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waktu sesi untuk kelas siswa ini sudah habis. Silakan atur ulang jam sesi terlebih dahulu jika ingin memberi waktu tambahan.',
            ], 422);
        }

        DB::transaction(function () use ($seleksi) {
            // 1. Hapus rekap nilai sementara agar status "Selesai" di dashboard siswa hilang
            Nilai::where('seleksi_ujian_id', $seleksi->id)->delete();

            // 2. Kembalikan status ke sedang_mengerjakan TANPA mereset pelanggaran_count
            // Data di tabel `menjawabs` juga tetap dipertahankan.
            $seleksi->update([
                'status'        => 'sedang_mengerjakan',
                'waktu_selesai' => null,
                'nilai_awal'    => 0,
                'nilai_akhir'   => 0,
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Ujian siswa berhasil dibuka kembali. Seluruh jawaban sebelumnya tetap tersimpan.',
        ]);
    }

    /**
     * Mengacak ulang Kode Buka Blokir (Unlock Code) untuk seluruh siswa pada ujian ini.
     */
    public function refreshUnlockCode(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $kodeBaru = strtoupper(Str::random(6));

        if ($ujian->id_grup_ujian) {
            Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->update(['unlock_code' => $kodeBaru]);
        } else {
            $ujian->update(['unlock_code' => $kodeBaru]);
        }

        return response()->json([
            'status' => 'success',
            'unlock_code' => $kodeBaru,
            'message' => "Kode buka blokir berhasil diperbarui menjadi {$kodeBaru}.",
        ]);
    }

    /**
     * Mengacak ulang token untuk sesi tertentu & opsional me-reset jam mulai sesi ke sekarang.
     */
    public function resetToken(Request $request, Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $nomorSesi = (int) $request->input('sesi', $ujian->sesi ?? 1);
        $mulaiSekarang = (bool) $request->input('mulai_sekarang', false);

        $tokenBaru = 'S' . $nomorSesi . '-' . strtoupper(Str::random(4));

        $query = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->where('sesi', $nomorSesi)
            : Ujian::where('id', $ujian->id);

        $updateData = ['token' => $tokenBaru];
        if ($mulaiSekarang) {
            $updateData['waktu_mulai_sesi'] = now();
        }

        $query->update($updateData);

        $waktuMulaiSesi = $mulaiSekarang ? now() : ($query->first()?->waktu_mulai_sesi ?? now());
        $waktuSelesaiSesi = Carbon::parse($waktuMulaiSesi)->addMinutes((int) $ujian->durasi_menit);

        return response()->json([
            'status' => 'success',
            'sesi' => $nomorSesi,
            'token_baru' => $tokenBaru,
            'jam_mulai' => Carbon::parse($waktuMulaiSesi)->format('H:i'),
            'jam_selesai' => $waktuSelesaiSesi->format('H:i'),
            'message' => "Token Sesi {$nomorSesi} berhasil diperbarui menjadi {$tokenBaru}.",
        ]);
    }

    /**
     * Mengatur pembagian Sesi untuk masing-masing kelas dalam satu grup ujian.
     * Contoh: 7-A (Sesi 1), 7-B (Sesi 1), 7-C (Sesi 2).
     */
    public function aturSesiKelas(Request $request, Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $sesiMap = $request->input('sesi_ujian', []); // [ujian_id => nomor_sesi]
        $jamSesiMap = $request->input('jam_sesi', []); // [nomor_sesi => '07:00']

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        DB::transaction(function () use ($grupUjians, $sesiMap, $jamSesiMap, $ujian) {
            // 1. Update nomor sesi tiap kelas
            foreach ($grupUjians as $item) {
                if (isset($sesiMap[$item->id])) {
                    $item->sesi = max(1, (int) $sesiMap[$item->id]);
                    $item->save();
                }
            }

            // 2. Samakan token dan jam mulai untuk semua kelas yang berada di sesi yang sama
            $groupedBySesi = $grupUjians->groupBy('sesi');
            $tanggalDasar = ($ujian->waktu_mulai ?? now())->format('Y-m-d');

            foreach ($groupedBySesi as $nomorSesi => $items) {
                $existingToken = $items->pluck('token')->filter(fn($t) => str_starts_with((string) $t, "S{$nomorSesi}-"))->first();
                $tokenSesi = $existingToken ?: ('S' . $nomorSesi . '-' . strtoupper(Str::random(4)));

                // Tentukan jam mulai sesi
                if (!empty($jamSesiMap[$nomorSesi])) {
                    $waktuMulaiSesi = Carbon::parse($tanggalDasar . ' ' . $jamSesiMap[$nomorSesi]);
                } else {
                    $baseStart = $ujian->waktu_mulai ?? now();
                    $waktuMulaiSesi = Carbon::parse($baseStart)->copy()->addMinutes(((int) $nomorSesi - 1) * (int) $ujian->durasi_menit);
                }

                foreach ($items as $item) {
                    $item->update([
                        'token' => $tokenSesi,
                        'waktu_mulai_sesi' => $waktuMulaiSesi,
                    ]);
                }
            }
        });

        return back()->with('success', 'Pembagian sesi kelas dan jadwal token berhasil diperbarui.');
    }

    /**
     * Halaman Rekap Nilai Satu Pintu (Seluruh Siswa di Kelas Target).
     */
    public function rekap(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $ujian->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
        ]);

        // Ambil seluruh kelas dalam grup ujian yang sama (atau ujian tunggal)
        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with(['pengampuKelas.kelas', 'soals'])->where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        $ujianIds = $grupUjians->pluck('id');
        $kelasIds = $grupUjians->pluck('pengampuKelas.kelas_id')->filter()->unique();

        // [UBAHAN 1] Ambil seluruh siswa dari kelas-kelas target melalui relasi anggotaKelases (filter agama jika mapel khusus agama)
        $mapelUjian = $ujian->pengampuKelas?->guruMapel?->mapel;
        $semuaSiswa = Siswa::with(['user', 'anggotaKelases.kelas'])
            ->forMapel($mapelUjian)
            ->whereHas('anggotaKelases', function ($sub) use ($kelasIds) {
                $sub->whereIn('kelas_id', $kelasIds);
            })
            ->get()
            ->unique('id');

        // Jika ujian ini adalah Remidi / Susulan, batasi daftar hanya untuk peserta_khusus
        if (in_array($ujian->jenis_ujian, ['remidi', 'susulan']) && !empty($ujian->peserta_khusus)) {
            $pesertaKhususIds = array_map('intval', $ujian->peserta_khusus);
            $semuaSiswa = $semuaSiswa->whereIn('id', $pesertaKhususIds)->values();
        }

        // Ambil sesi pengerjaan & jawaban siswa
        $sesiMap = SeleksiUjian::whereIn('ujian_id', $ujianIds)->get()->keyBy('siswa_id');
        $soalIds = Soal::whereIn('ujian_id', $ujianIds)->pluck('id');
        $jawabanAll = Menjawab::whereIn('soal_id', $soalIds)->get()->groupBy('siswa_id');

        $kkm = (float) ($ujian->kkm ?? 75);

        $rekapSiswa = $semuaSiswa->map(function ($siswa) use ($grupUjians, $sesiMap, $jawabanAll, $kkm, $ujian) {
            $sesi = $sesiMap->get($siswa->id);
            $ujianSiswa = $sesi
                ? $grupUjians->firstWhere('id', $sesi->ujian_id)
                : $grupUjians->first();

            $soals = $ujianSiswa ? $ujianSiswa->soals : collect();
            // Hanya hitung soal yang valid (bobot > 0)
            $soalValid = $soals->filter(fn($s) => (float) ($s->bobot ?? 0) > 0);
            $soalPG = $soalValid->whereIn('tipe_soal', ['single_choice', 'multiple_choice']);
            $soalEssay = $soalValid->where('tipe_soal', 'essay');

            $jawabanSiswa = ($jawabanAll->get($siswa->id) ?? collect())->keyBy('soal_id');

            $sudahSelesai = $sesi && ($sesi->status === 'selesai' || !is_null($sesi->waktu_selesai));
            $sedangMengerjakan = $sesi && !$sudahSelesai;

            // Hitung Statistik PG & Nilai PG
            $pgBenar = 0;
            $pgSalah = 0;
            $skorPG = 0;
            $bobotMaksPG = $soalPG->sum('bobot');

            foreach ($soalPG as $s) {
                $j = $jawabanSiswa->get($s->id);
                $hasil = $this->hitungSkorSatuSoal($s, $j);
                $skorPG += $hasil['skor'];
                if ($hasil['is_benar']) {
                    $pgBenar++;
                } else {
                    $pgSalah++;
                }
            }

            // Cek Status Koreksi Essay
            $totalSoalEssay = $soalEssay->count();
            $essaySudahDinilai = 0;
            $skorEssay = 0;
            $bobotMaksEssay = $soalEssay->sum('bobot');

            foreach ($soalEssay as $se) {
                $j = $jawabanSiswa->get($se->id);
                if ($j && !is_null($j->skor_essay)) {
                    $essaySudahDinilai++;
                    $skorEssay += (float) $j->skor_essay;
                }
            }

            $menungguKoreksiEssay = $sudahSelesai && ($totalSoalEssay > 0) && ($essaySudahDinilai < $totalSoalEssay);

            // Tentukan Nilai Akhir & Status KKM
            $totalBobot = ($bobotMaksPG + $bobotMaksEssay) ?: 100;
            $nilaiKalkulasi = round((($skorPG + $skorEssay) / $totalBobot) * 100, 2);
            if ($nilaiKalkulasi > 100)
                $nilaiKalkulasi = 100;

            // Jika sudah ada nilai remidi tersimpan di seleksi_ujians, gunakan nilai_akhir tersebut
            $nilaiAkhir = ($sesi && $sesi->jumlah_remidi > 0)
                ? (float) $sesi->nilai_akhir
                : $nilaiKalkulasi;

            if (!$sudahSelesai) {
                $statusKategori = 'belum_ujian'; // Abu-abu (Susulan)
                $statusLabel = $sedangMengerjakan ? 'Sedang Ujian' : 'Belum Mengerjakan';
            } elseif ($menungguKoreksiEssay) {
                $statusKategori = 'menunggu_essay'; // Kuning
                $statusLabel = "Menunggu Koreksi Essay ({$essaySudahDinilai}/{$totalSoalEssay})";
            } elseif ($nilaiAkhir >= $kkm) {
                $statusKategori = 'tuntas'; // Hijau
                $statusLabel = 'Tuntas';
            } else {
                $statusKategori = 'remidi'; // Merah
                $statusLabel = 'Remidi / Tidak Tuntas';
            }

            // [UBAHAN 2] Ambil nama kelas siswa dari $ujianSiswa atau anggotaKelases
            $namaKelasSiswa = $ujianSiswa?->pengampuKelas?->kelas?->nama_kelas
                ?? $siswa->anggotaKelases->last()?->kelas?->nama_kelas
                ?? '-';

            return [
                'siswa_id' => $siswa->id,
                'ujian_id' => $ujianSiswa?->id ?? $ujian->id,
                'seleksi_id' => $sesi?->id,
                'nama' => $siswa->user->name ?? $siswa->nama_lengkap ?? $siswa->nama ?? ('Siswa #' . $siswa->id),
                'nisn' => $siswa->nis ?? $siswa->nisn ?? $siswa->user?->username ?? '-',
                'kelas' => $namaKelasSiswa, // [UBAHAN 3] Pakai variabel $namaKelasSiswa
                'sudah_selesai' => $sudahSelesai,
                'pg_benar' => $pgBenar,
                'pg_salah' => $pgSalah,
                'total_pg' => $soalPG->count(),
                'nilai_pg' => round($skorPG, 2),
                'bobot_pg' => round($bobotMaksPG, 2),
                'total_essay' => $totalSoalEssay,
                'essay_dinilai' => $essaySudahDinilai,
                'nilai_essay' => round($skorEssay, 2),
                'bobot_essay' => round($bobotMaksEssay, 2),
                'menunggu_koreksi_essay' => $menungguKoreksiEssay,
                'nilai_awal' => $sesi ? (float) $sesi->nilai_awal : 0,
                'nilai_akhir' => $nilaiAkhir,
                'jumlah_remidi' => $sesi ? (int) $sesi->jumlah_remidi : 0,
                'status_kategori' => $statusKategori,
                'status_label' => $statusLabel,
            ];
        })->sortBy('nama')->values();

        // Statistik Ringkas untuk Header Filter
        $ringkasan = [
            'total' => $rekapSiswa->count(),
            'menunggu_essay' => $rekapSiswa->where('status_kategori', 'menunggu_essay')->count(),
            'remidi' => $rekapSiswa->where('status_kategori', 'remidi')->count(),
            'tuntas' => $rekapSiswa->where('status_kategori', 'tuntas')->count(),
            'belum_ujian' => $rekapSiswa->where('status_kategori', 'belum_ujian')->count(),
            'ada_soal_essay' => $ujian->soals->where('tipe_soal', 'essay')->count() > 0,
        ];

        return view('guru.kelola_ujian.rekap', compact('ujian', 'rekapSiswa', 'ringkasan', 'kkm'));
    }

    /**
     * Halaman Menu Laporan & Rekap Nilai (Preview Leger Semester & Detail Satu Ujian).
     */
    public function laporan(Request $request)
    {
        $data = $this->bangunDataLaporan($request);

        return view('guru.kelola_ujian.laporan', $data);
    }

    /**
     * Generate & Download File PDF untuk Leger Mapel (Multi-Ujian) atau Berita Acara (Satu Ujian).
     */
    public function exportLaporanPdf(Request $request)
    {
        $data = $this->bangunDataLaporan($request);

        if (!$data['selectedPengampu']) {
            return redirect()->route('guru.ujian.laporan')
                ->with('error', 'Silakan pilih Mata Pelajaran dan Kelas terlebih dahulu sebelum mencetak PDF.');
        }

        // Tentukan orientasi kertas otomatis:
        // - Mode Leger: Landscape jika jumlah ujian > 4 (atau default landscape agar leluasa)
        // - Mode Detail Satu Ujian: Portrait
        $orientasi = 'portrait';
        if ($data['mode'] === 'leger' && $data['ujianRegulers']->count() > 4) {
            $orientasi = 'landscape';
        } elseif ($data['mode'] === 'leger') {
            $orientasi = 'landscape';
        }

        $namaKelas = Str::slug($data['selectedPengampu']->kelas->nama_kelas ?? 'kelas');
        $namaMapel = Str::slug($data['selectedPengampu']->guruMapel->mapel->nama_mapel ?? 'mapel');
        $thn = str_replace(['/', '\\'], '-', $data['selectedTahunAjaran']);

        $namaFile = $data['mode'] === 'leger'
            ? "Leger-Nilai-{$namaMapel}-{$namaKelas}-{$thn}-" . ucfirst($data['selectedSemester']) . ".pdf"
            : "Berita-Acara-Ujian-{$namaMapel}-{$namaKelas}-{$thn}.pdf";

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('guru.kelola_ujian.laporan_pdf', $data)
                ->setPaper('a4', $orientasi);

            return $pdf->download($namaFile);
        }

        // Fallback jika paket dompdf belum terinstal: buka tampilan siap cetak browser
        return view('guru.kelola_ujian.laporan_pdf', array_merge($data, ['autoPrint' => true]));
    }

    /**
     * Helper Internal: Menyusun data Leger Multi-Ujian & Detail Satu Ujian
     * dengan filter anggota_kelases dan pemetaan rentang bulan semester otomatis.
     */
    private function bangunDataLaporan(Request $request): array
    {
        $guruId = auth()->user()->guru_id;

        // 1. Ambil seluruh kelas & mapel yang diampu oleh guru
        $pengampuList = PengampuKelas::with(['guruMapel.mapel', 'guruMapel.guru.user', 'kelas'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->get()
            ->filter(fn($item) => $item->guruMapel?->mapel && $item->kelas)
            ->values();

        $kelasIdsGuru = $pengampuList->pluck('kelas_id')->unique();

        // 2. Ambil daftar Tahun Ajaran unik dari tabel academic_periods yang dipakai oleh anggota_kelases
        $daftarTahunAjaran = DB::table('anggota_kelases')
            ->join('academic_periods', 'anggota_kelases.academic_period_id', '=', 'academic_periods.id')
            ->whereIn('anggota_kelases.kelas_id', $kelasIdsGuru)
            ->distinct()
            ->orderByDesc('academic_periods.tahun_ajaran')
            ->pluck('academic_periods.tahun_ajaran')
            ->values();

        if ($daftarTahunAjaran->isEmpty()) {
            $bulanSekarang = (int) now()->format('n');
            $thnSekarang = (int) now()->format('Y');
            $defaultTa = $bulanSekarang >= 7
                ? "{$thnSekarang}/" . ($thnSekarang + 1)
                : ($thnSekarang - 1) . "/{$thnSekarang}";
            $daftarTahunAjaran = collect([$defaultTa]);
        }

        // 3. Tentukan parameter filter yang sedang aktif
        $selectedTahunAjaran = $request->input('tahun_ajaran', $daftarTahunAjaran->first());
        $selectedSemester = strtolower($request->input('semester', (int) now()->format('n') >= 7 ? 'ganjil' : 'genap'));
        if (!in_array($selectedSemester, ['ganjil', 'genap'])) {
            $selectedSemester = 'ganjil';
        }

        $selectedPengampuId = $request->input('pengampu_kelas_id', $pengampuList->first()?->id);
        $selectedPengampu = $pengampuList->firstWhere('id', (int) $selectedPengampuId) ?? $pengampuList->first();

        $mode = $request->input('mode', 'leger'); // 'leger' atau 'detail'
        if (!in_array($mode, ['leger', 'detail'])) {
            $mode = 'leger';
        }

        // 4. Mapping Otomatis Rentang Bulan Berdasarkan Tahun Ajaran & Semester
        // Format tahun_ajaran: "2026/2027" -> Ganjil: Jul-Des 2026 | Genap: Jan-Jun 2027
        $partsTa = preg_split('/[\/\-]/', (string) $selectedTahunAjaran);
        $tahunAwal = isset($partsTa[0]) && is_numeric($partsTa[0]) ? (int) $partsTa[0] : (int) now()->format('Y');
        $tahunAkhir = isset($partsTa[1]) && is_numeric($partsTa[1]) ? (int) $partsTa[1] : ($tahunAwal + 1);

        if ($selectedSemester === 'ganjil') {
            $rentangMulai = Carbon::create($tahunAwal, 7, 1, 0, 0, 0);
            $rentangSelesai = Carbon::create($tahunAwal, 12, 31, 23, 59, 59);
        } else {
            $rentangMulai = Carbon::create($tahunAkhir, 1, 1, 0, 0, 0);
            $rentangSelesai = Carbon::create($tahunAkhir, 6, 30, 23, 59, 59);
        }

        $ujianRegulers = collect();
        $selectedUjian = null;
        $barisLeger = collect();
        $barisDetail = collect();
        $qualityGate = [
            'menunggu_essay' => 0,
            'belum_ujian' => 0,
        ];

        if ($selectedPengampu) {
            // 5. Ambil seluruh Ujian Reguler pada PengampuKelas & rentang semester tersebut
            $ujianRegulers = Ujian::with('soals')
                ->where('pengampu_kelas_id', $selectedPengampu->id)
                ->where(function ($q) {
                    $q->where('jenis_ujian', 'reguler')->orWhereNull('jenis_ujian');
                })
                ->whereIn('status', ['published', 'completed', 'unpublished'])
                ->where(function ($q) use ($rentangMulai, $rentangSelesai) {
                    $q->whereBetween('waktu_mulai', [$rentangMulai, $rentangSelesai])
                        ->orWhere(function ($sub) use ($rentangMulai, $rentangSelesai) {
                            $sub->whereNull('waktu_mulai')
                                ->whereBetween('created_at', [$rentangMulai, $rentangSelesai]);
                        });
                })
                ->orderBy('waktu_mulai', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $selectedUjianId = $request->input('ujian_id', $ujianRegulers->first()?->id);
            $selectedUjian = $ujianRegulers->firstWhere('id', (int) $selectedUjianId) ?? $ujianRegulers->first();

            // 6. Ambil daftar siswa murni dari anggota_kelases sesuai Kelas, Tahun Ajaran, dan Semester (filter agama jika mapel khusus agama)
            $mapelPengampu = $selectedPengampu->guruMapel?->mapel;
            $daftarSiswa = Siswa::with(['user'])
                ->forMapel($mapelPengampu)
                ->whereHas('anggotaKelases', function ($q) use ($selectedPengampu, $selectedTahunAjaran, $selectedSemester) {
                    $q->where('kelas_id', $selectedPengampu->kelas_id)
                        ->whereHas('academicPeriod', function ($ap) use ($selectedTahunAjaran, $selectedSemester) {
                            $ap->where('tahun_ajaran', $selectedTahunAjaran)
                                ->whereRaw('LOWER(semester) = ?', [$selectedSemester]);
                        });
                })
                ->get()
                ->unique('id')
                ->sortBy(fn($s) => strtolower($s->user->name ?? $s->nama_lengkap ?? $s->nama ?? ''))
                ->values();

            // Fallback: jika di anggota_kelases belum ada yang cocok dengan semester tsb, tarik berdasarkan kelas_id saja
            if ($daftarSiswa->isEmpty()) {
                $daftarSiswa = Siswa::with(['user'])
                    ->forMapel($mapelPengampu)
                    ->whereHas('anggotaKelases', function ($q) use ($selectedPengampu) {
                        $q->where('kelas_id', $selectedPengampu->kelas_id);
                    })
                    ->get()
                    ->unique('id')
                    ->sortBy(fn($s) => strtolower($s->user->name ?? $s->nama_lengkap ?? $s->nama ?? ''))
                    ->values();
            }

            $regulerIds = $ujianRegulers->pluck('id');

            // Ambil sesi pengerjaan ujian reguler
            $sesiRegulerAll = SeleksiUjian::whereIn('ujian_id', $regulerIds)->get();

            // Ambil ujian anak (remidi / susulan) untuk menandai kode (R) dan (S)
            $ujianAnakList = Ujian::whereIn('parent_ujian_id', $regulerIds)
                ->whereIn('jenis_ujian', ['remidi', 'susulan'])
                ->get();

            $sesiAnakSelesai = SeleksiUjian::whereIn('ujian_id', $ujianAnakList->pluck('id'))
                ->where(function ($q) {
                    $q->where('status', 'selesai')->orWhereNotNull('waktu_selesai');
                })
                ->get();

            // Ambil seluruh jawaban siswa untuk pengecekan koreksi essay & kalkulasi detail
            $semuaSoalIds = Soal::whereIn('ujian_id', $regulerIds)->pluck('id');
            $jawabanAll = Menjawab::whereIn('soal_id', $semuaSoalIds)->get()->groupBy('siswa_id');

            // =========================================================
            // A. SUSUN DATA MODE LEGER SEMESTER (MULTI-UJIAN)
            // =========================================================
            $barisLeger = $daftarSiswa->map(function ($siswa) use ($ujianRegulers, $sesiRegulerAll, $ujianAnakList, $sesiAnakSelesai, $jawabanAll, &$qualityGate) {
                $jawabanSiswa = ($jawabanAll->get($siswa->id) ?? collect())->keyBy('soal_id');
                $nilaiPerUjian = [];
                $kumpulanNilaiValid = [];

                foreach ($ujianRegulers as $ujianItem) {
                    $sesi = $sesiRegulerAll
                        ->where('ujian_id', $ujianItem->id)
                        ->where('siswa_id', $siswa->id)
                        ->first();

                    $sudahSelesai = $sesi && ($sesi->status === 'selesai' || !is_null($sesi->waktu_selesai));

                    // Cek apakah ada soal essay yang belum dikoreksi
                    $soalEssays = $ujianItem->soals->where('tipe_soal', 'essay');
                    $essayBelumDinilai = false;
                    if ($sudahSelesai && $soalEssays->isNotEmpty()) {
                        foreach ($soalEssays as $se) {
                            $j = $jawabanSiswa->get($se->id);
                            if (!$j || is_null($j->skor_essay)) {
                                $essayBelumDinilai = true;
                                break;
                            }
                        }
                    }

                    // Deteksi apakah nilai berasal dari Remidi (R) atau Susulan (S)
                    $anakIdsRemidi = $ujianAnakList
                        ->where('parent_ujian_id', $ujianItem->id)
                        ->where('jenis_ujian', 'remidi')
                        ->pluck('id');

                    $anakIdsSusulan = $ujianAnakList
                        ->where('parent_ujian_id', $ujianItem->id)
                        ->where('jenis_ujian', 'susulan')
                        ->pluck('id');

                    $ikutRemidi = ($sesi && (int) $sesi->jumlah_remidi > 0)
                        || $sesiAnakSelesai->whereIn('ujian_id', $anakIdsRemidi)->where('siswa_id', $siswa->id)->isNotEmpty();

                    $ikutSusulan = $sesiAnakSelesai->whereIn('ujian_id', $anakIdsSusulan)->where('siswa_id', $siswa->id)->isNotEmpty();

                    $kodeAsal = null;
                    if ($ikutRemidi) {
                        $kodeAsal = 'R';
                    } elseif ($ikutSusulan) {
                        $kodeAsal = 'S';
                    }

                    if (!$sudahSelesai) {
                        $qualityGate['belum_ujian']++;
                        $nilaiPerUjian[$ujianItem->id] = [
                            'status' => 'belum_ujian',
                            'nilai' => null,
                            'nilai_awal' => null,
                            'kode' => null,
                            'tuntas' => false,
                        ];
                    } elseif ($essayBelumDinilai) {
                        $qualityGate['menunggu_essay']++;
                        $nilaiPerUjian[$ujianItem->id] = [
                            'status' => 'menunggu_essay',
                            'nilai' => null,
                            'nilai_awal' => (float) $sesi->nilai_awal,
                            'kode' => $kodeAsal,
                            'tuntas' => false,
                        ];
                    } else {
                        $skorFinal = round((float) $sesi->nilai_akhir, 2);
                        $kkmUjian = (float) ($ujianItem->kkm ?? 75);
                        $kumpulanNilaiValid[] = $skorFinal;

                        $nilaiPerUjian[$ujianItem->id] = [
                            'status' => 'selesai',
                            'nilai' => $skorFinal,
                            'nilai_awal' => round((float) $sesi->nilai_awal, 2),
                            'kode' => $kodeAsal,
                            'tuntas' => $skorFinal >= $kkmUjian,
                        ];
                    }
                }

                $rataRata = count($kumpulanNilaiValid) > 0
                    ? round(array_sum($kumpulanNilaiValid) / count($kumpulanNilaiValid), 2)
                    : null;

                return [
                    'siswa_id' => $siswa->id,
                    'nama' => $siswa->user->name ?? $siswa->nama_lengkap ?? $siswa->nama ?? ('Siswa #' . $siswa->id),
                    'nisn' => $siswa->nis ?? $siswa->nisn ?? $siswa->user?->username ?? '-',
                    'nilai_per_ujian' => $nilaiPerUjian,
                    'rata_rata' => $rataRata,
                ];
            });

            // =========================================================
            // B. SUSUN DATA MODE DETAIL SATU UJIAN (BERITA ACARA)
            // =========================================================
            if ($selectedUjian) {
                $kkmDetail = (float) ($selectedUjian->kkm ?? 75);
                $soalsDetail = $selectedUjian->soals;
                $soalPG = $soalsDetail->whereIn('tipe_soal', ['single_choice', 'multiple_choice']);
                $soalEssay = $soalsDetail->where('tipe_soal', 'essay');

                $anakRemidiIds = $ujianAnakList->where('parent_ujian_id', $selectedUjian->id)->where('jenis_ujian', 'remidi')->pluck('id');
                $anakSusulanIds = $ujianAnakList->where('parent_ujian_id', $selectedUjian->id)->where('jenis_ujian', 'susulan')->pluck('id');

                $barisDetail = $daftarSiswa->map(function ($siswa) use ($selectedUjian, $sesiRegulerAll, $sesiAnakSelesai, $anakRemidiIds, $anakSusulanIds, $soalPG, $soalEssay, $jawabanAll, $kkmDetail) {
                    $sesi = $sesiRegulerAll
                        ->where('ujian_id', $selectedUjian->id)
                        ->where('siswa_id', $siswa->id)
                        ->first();

                    $jawabanSiswa = ($jawabanAll->get($siswa->id) ?? collect())->keyBy('soal_id');
                    $sudahSelesai = $sesi && ($sesi->status === 'selesai' || !is_null($sesi->waktu_selesai));

                    $pgBenar = 0;
                    $pgSalah = 0;
                    $skorPG = 0;
                    foreach ($soalPG as $s) {
                        $hasil = $this->hitungSkorSatuSoal($s, $jawabanSiswa->get($s->id));
                        $skorPG += $hasil['skor'];
                        if ($hasil['is_benar']) {
                            $pgBenar++;
                        } else {
                            $pgSalah++;
                        }
                    }

                    $essayDinilai = 0;
                    $skorEssay = 0;
                    foreach ($soalEssay as $se) {
                        $j = $jawabanSiswa->get($se->id);
                        if ($j && !is_null($j->skor_essay)) {
                            $essayDinilai++;
                            $skorEssay += (float) $j->skor_essay;
                        }
                    }

                    $menungguEssay = $sudahSelesai && ($soalEssay->count() > 0) && ($essayDinilai < $soalEssay->count());

                    $ikutRemidi = ($sesi && (int) $sesi->jumlah_remidi > 0)
                        || $sesiAnakSelesai->whereIn('ujian_id', $anakRemidiIds)->where('siswa_id', $siswa->id)->isNotEmpty();

                    $ikutSusulan = $sesiAnakSelesai->whereIn('ujian_id', $anakSusulanIds)->where('siswa_id', $siswa->id)->isNotEmpty();

                    $kodeAsal = $ikutRemidi ? 'R' : ($ikutSusulan ? 'S' : null);

                    $nilaiAwal = $sesi ? round((float) $sesi->nilai_awal, 2) : null;
                    $nilaiAkhir = $sesi ? round((float) $sesi->nilai_akhir, 2) : null;

                    if (!$sudahSelesai) {
                        $statusKategori = 'belum_ujian';
                        $statusLabel = 'Belum Ujian';
                    } elseif ($menungguEssay) {
                        $statusKategori = 'menunggu_essay';
                        $statusLabel = 'Menunggu Koreksi Essay';
                    } elseif ($nilaiAkhir >= $kkmDetail) {
                        $statusKategori = 'tuntas';
                        $statusLabel = 'Tuntas';
                    } else {
                        $statusKategori = 'remidi';
                        $statusLabel = 'Belum Tuntas';
                    }

                    return [
                        'siswa_id' => $siswa->id,
                        'nama' => $siswa->user->name ?? $siswa->nama_lengkap ?? $siswa->nama ?? ('Siswa #' . $siswa->id),
                        'nisn' => $siswa->nis ?? $siswa->nisn ?? $siswa->user?->username ?? '-',
                        'sudah_selesai' => $sudahSelesai,
                        'pg_benar' => $pgBenar,
                        'pg_salah' => $pgSalah,
                        'total_pg' => $soalPG->count(),
                        'skor_pg' => round($skorPG, 2),
                        'total_essay' => $soalEssay->count(),
                        'skor_essay' => round($skorEssay, 2),
                        'menunggu_essay' => $menungguEssay,
                        'nilai_awal' => $nilaiAwal,
                        'nilai_akhir' => $nilaiAkhir,
                        'kode' => $kodeAsal,
                        'status_kategori' => $statusKategori,
                        'status_label' => $statusLabel,
                    ];
                });
            }
        }

        return [
            'pengampuList' => $pengampuList,
            'daftarTahunAjaran' => $daftarTahunAjaran,
            'selectedTahunAjaran' => $selectedTahunAjaran,
            'selectedSemester' => $selectedSemester,
            'selectedPengampu' => $selectedPengampu,
            'mode' => $mode,
            'ujianRegulers' => $ujianRegulers,
            'selectedUjian' => $selectedUjian,
            'barisLeger' => $barisLeger,
            'barisDetail' => $barisDetail,
            'qualityGate' => $qualityGate,
            'rentangMulai' => $rentangMulai,
            'rentangSelesai' => $rentangSelesai,
        ];
    }

    /**
     * Halaman Koreksi Essay Massal (Per Nomor Soal untuk Semua Siswa - SpeedGrader).
     */
    public function koreksiEssay(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with(['pengampuKelas.kelas', 'soals'])->where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        $ujianIds = $grupUjians->pluck('id');

        // Ambil daftar soal essay dari ujian utama (diurutkan sesuai nomor soal)
        $soalEssayList = $ujian->soals()
            ->where('tipe_soal', 'essay')
            ->orderBy('urutan', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Ambil daftar siswa yang sudah menyelesaikan ujian
        $sesiSelesai = SeleksiUjian::with(['siswa.user', 'siswa.anggotaKelases.kelas'])
            ->whereIn('ujian_id', $ujianIds)
            ->where(function ($q) {
                $q->where('status', 'selesai')->orWhereNotNull('waktu_selesai');
            })
            ->get();

        $siswaIds = $sesiSelesai->pluck('siswa_id')->unique();

        // Map soal berdasarkan urutan agar sinkron antar kelas dalam 1 grup ujian
        $soalsSemuaKelas = Soal::whereIn('ujian_id', $ujianIds)
            ->where('tipe_soal', 'essay')
            ->get();

        $jawabanEssayAll = Menjawab::whereIn('soal_id', $soalsSemuaKelas->pluck('id'))
            ->whereIn('siswa_id', $siswaIds)
            ->get();

        // Susun struktur data matriks untuk Alpine.js: per Soal Essay -> daftar semua Siswa
        $dataKoreksi = $soalEssayList->values()->map(function ($soalUtama, $idx) use ($sesiSelesai, $soalsSemuaKelas, $jawabanEssayAll) {
            $urutan = $soalUtama->urutan ?? ($idx + 1);

            $pesertaItems = $sesiSelesai->map(function ($sesi) use ($soalUtama, $urutan, $soalsSemuaKelas, $jawabanEssayAll) {
                $siswa = $sesi->siswa;
                // Cari ID soal essay yang sesuai dengan ujian kelas siswa tersebut
                $soalKelasIni = $soalsSemuaKelas
                    ->where('ujian_id', $sesi->ujian_id)
                    ->where('urutan', $urutan)
                    ->first() ?? $soalUtama;

                $jawab = $jawabanEssayAll
                    ->where('soal_id', $soalKelasIni->id)
                    ->where('siswa_id', $sesi->siswa_id)
                    ->first();

                return [
                    'siswa_id' => $sesi->siswa_id,
                    'ujian_id' => $sesi->ujian_id,
                    'soal_id' => $soalKelasIni->id,
                    'nama' => $siswa?->user?->name ?? $siswa?->nama_lengkap ?? ('Siswa #' . $sesi->siswa_id),
                    'kelas' => $siswa?->anggotaKelases?->last()?->kelas?->nama_kelas ?? '-',
                    'jawaban_teks' => $jawab?->jawaban_dipilih ?? '',
                    'skor' => $jawab && !is_null($jawab->skor_essay) ? (float) $jawab->skor_essay : '',
                    'sudah_dinilai' => $jawab && !is_null($jawab->skor_essay),
                    'catatan_guru' => $jawab?->catatan_guru ?? '',
                ];
            })->sortBy('nama')->values();

            $kunciTeks = is_array($soalUtama->kunci_jawaban)
                ? implode("\n", $soalUtama->kunci_jawaban)
                : ($soalUtama->kunci_jawaban ?? '');

            return [
                'nomor_essay' => $idx + 1,
                'nomor_asli' => $urutan,
                'pertanyaan' => $soalUtama->pertanyaan,
                'gambar' => $soalUtama->gambar ? asset('storage/' . $soalUtama->gambar) : null,
                'bobot_maks' => (float) ($soalUtama->bobot ?? 10),
                'kunci_jawaban' => $kunciTeks,
                'pesertas' => $pesertaItems,
            ];
        });

        return view('guru.kelola_ujian.koreksi_essay', compact('ujian', 'dataKoreksi'));
    }

    /**
     * Endpoint AJAX Auto-Save & Simpan Nilai Essay (Dipakai di Koreksi Massal & Edit Individual).
     */
    public function simpanNilaiEssay(Request $request, Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $validated = $request->validate([
            'ujian_id' => 'required|integer|exists:ujians,id',
            'siswa_id' => 'required|integer|exists:siswas,id',
            'soal_id' => 'required|integer|exists:soals,id',
            'skor' => 'required|numeric|min:0',
            'catatan_guru' => 'nullable|string|max:500',
        ]);

        $soal = Soal::where('ujian_id', $validated['ujian_id'])->findOrFail($validated['soal_id']);
        $bobotMaks = (float) ($soal->bobot ?? 100);
        $skorValid = min($bobotMaks, max(0, (float) $validated['skor']));

        // 1. Simpan atau perbarui skor essay pada tabel menjawabs
        Menjawab::updateOrCreate(
            [
                'soal_id' => $soal->id,
                'siswa_id' => $validated['siswa_id'],
            ],
            [
                'skor_essay' => $skorValid,
                'catatan_guru' => $validated['catatan_guru'] ?? null,
                'benar' => $skorValid >= ($bobotMaks * 0.5),
            ]
        );

        // 2. Hitung ulang Nilai Akhir siswa (PG + Essay) & sinkronkan ke ujian induk jika remidi/susulan
        $hasilRekalkulasi = $this->rekalkulasiNilaiSiswa($validated['ujian_id'], $validated['siswa_id']);

        return response()->json([
            'status' => 'success',
            'skor_disimpan' => $skorValid,
            'nilai_akhir' => $hasilRekalkulasi['nilai_akhir'],
            'status_kkm' => $hasilRekalkulasi['status_kkm'],
            'message' => 'Nilai essay berhasil disimpan.',
        ]);
    }

    /**
     * Detail Lembar Jawaban Individual Siswa (Untuk Lihat & Edit Nilai Essay Kapan Saja).
     */
    public function detailJawabanSiswa(Ujian $ujian, Siswa $siswa)
    {
        $this->authorizeGuruUjian($ujian);

        $ujianIds = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->pluck('id')
            : collect([$ujian->id]);

        $sesi = SeleksiUjian::whereIn('ujian_id', $ujianIds)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        $ujianSiswa = Ujian::with(['soals' => fn($q) => $q->orderBy('urutan')->orderBy('id'), 'pengampuKelas.kelas'])
            ->findOrFail($sesi->ujian_id);

        $jawabanMap = Menjawab::where('siswa_id', $siswa->id)
            ->whereIn('soal_id', $ujianSiswa->soals->pluck('id'))
            ->get()
            ->keyBy('soal_id');

        $daftarJawaban = $ujianSiswa->soals->map(function ($soal, $idx) use ($jawabanMap) {
            $jawab = $jawabanMap->get($soal->id);
            $hasil = $this->hitungSkorSatuSoal($soal, $jawab);

            return [
                'soal' => $soal,
                'nomor' => $idx + 1,
                'jawaban_teks' => $jawab?->jawaban_dipilih ?? '',
                'skor_diperoleh' => $hasil['skor'],
                'is_benar' => $hasil['is_benar'],
                'sudah_dinilai' => $soal->tipe_soal !== 'essay' || ($jawab && !is_null($jawab->skor_essay)),
                'catatan_guru' => $jawab?->catatan_guru ?? '',
            ];
        });

        return view('guru.kelola_ujian.detail_jawaban', compact('ujian', 'ujianSiswa', 'siswa', 'sesi', 'daftarJawaban'));
    }

    /**
     * Membuat Ujian Remidi atau Susulan dari Halaman Rekap Nilai.
     */
    public function buatRemidiSusulan(Request $request, Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $validated = $request->validate([
            'jenis' => 'required|in:remidi,susulan',
            'judul' => 'required|string|max:120',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'integer|exists:siswas,id',
            'durasi_menit' => 'required|integer|min:1|max:999',
            'waktu_mulai' => 'required|date',
            'deadline' => 'required|date|after:waktu_mulai',
            'kebijakan_remidi' => 'nullable|required_if:jenis,remidi|in:kkm,terbaik,rata_rata',
            'sumber_soal' => 'required|in:salin_induk,buat_baru',
        ], [
            'siswa_ids.required' => 'Pilih minimal satu siswa peserta remidi/susulan.',
        ]);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with(['pengampuKelas.kelas', 'soals'])->where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        $siswaIdsTerpilih = array_map('intval', $validated['siswa_ids']);
        $sesiIndukMap = SeleksiUjian::whereIn('ujian_id', $grupUjians->pluck('id'))
            ->whereIn('siswa_id', $siswaIdsTerpilih)
            ->get()
            ->keyBy('siswa_id');

        $siswaList = Siswa::with('anggotaKelases')->whereIn('id', $siswaIdsTerpilih)->get();

        // Kelompokkan siswa terpilih berdasarkan kelas ujian induk masing-masing
        $pesertaPerUjianInduk = [];
        foreach ($siswaList as $siswa) {
            $sesiInduk = $sesiIndukMap->get($siswa->id);
            if ($sesiInduk) {
                $targetUjianInduk = $grupUjians->firstWhere('id', $sesiInduk->ujian_id);
            } else {
                $kelasSiswaId = $siswa->anggotaKelases->last()?->kelas_id;
                $targetUjianInduk = $grupUjians->firstWhere('pengampuKelas.kelas_id', $kelasSiswaId) ?? $grupUjians->first();
            }

            if ($targetUjianInduk) {
                $pesertaPerUjianInduk[$targetUjianInduk->id]['ujian'] = $targetUjianInduk;
                $pesertaPerUjianInduk[$targetUjianInduk->id]['siswa_ids'][] = $siswa->id;
            }
        }

        $ujianBaruPertama = null;

        DB::transaction(function () use ($pesertaPerUjianInduk, $validated, &$ujianBaruPertama) {
            $grupBaruId = (string) Str::uuid();
            $tokenSesi1 = 'S1-' . strtoupper(Str::random(4));
            $unlockCode = strtoupper(Str::random(6));
            $waktuMulai = Carbon::parse($validated['waktu_mulai']);
            $deadline = Carbon::parse($validated['deadline']);

            // Jika soal langsung disalin dari induk, ujian bisa langsung live/scheduled; jika buat baru, simpan sebagai draft
            if ($validated['sumber_soal'] === 'buat_baru') {
                $statusBaru = 'draft';
            } else {
                $statusBaru = now()->lt($waktuMulai) ? 'scheduled' : 'published';
            }

            foreach ($pesertaPerUjianInduk as $dataKelompok) {
                /** @var Ujian $induk */
                $induk = $dataKelompok['ujian'];
                $daftarSiswaKelas = array_values(array_unique($dataKelompok['siswa_ids']));

                $ujianBaru = Ujian::create([
                    'id_grup_ujian' => $grupBaruId,
                    'judul' => $validated['judul'],
                    'kisi_kisi' => $induk->kisi_kisi,
                    'pengampu_kelas_id' => $induk->pengampu_kelas_id,
                    'sesi' => 1,
                    'durasi_menit' => (int) $validated['durasi_menit'],
                    'kkm' => $induk->kkm,
                    'waktu_mulai' => $waktuMulai,
                    'waktu_mulai_sesi' => $waktuMulai,
                    'deadline' => $deadline,
                    'status' => $statusBaru,
                    'jenis_ujian' => $validated['jenis'],
                    'parent_ujian_id' => $induk->id,
                    'kebijakan_remidi' => $validated['jenis'] === 'remidi' ? $validated['kebijakan_remidi'] : null,
                    'peserta_khusus' => $daftarSiswaKelas,
                    'token' => $tokenSesi1,
                    'unlock_code' => $unlockCode,
                    'dipublikasi_at' => $statusBaru === 'published' ? now() : null,
                ]);

                if (!$ujianBaruPertama) {
                    $ujianBaruPertama = $ujianBaru;
                }

                // Salin soal dari ujian induk jika guru memilih "salin_induk"
                if ($validated['sumber_soal'] === 'salin_induk') {
                    foreach ($induk->soals as $soalLama) {
                        $soalBaru = $soalLama->replicate(['created_at', 'updated_at']);
                        $soalBaru->ujian_id = $ujianBaru->id;
                        $soalBaru->save();
                    }
                }
            }
        });

        // Jika guru memilih "buat_baru", langsung arahkan ke halaman Edit Soal untuk menyusun soal paket baru
        if ($validated['sumber_soal'] === 'buat_baru' && $ujianBaruPertama) {
            return redirect()->route('guru.ujian.edit', $ujianBaruPertama->id)
                ->with('success', 'Kerangka ujian ' . ucfirst($validated['jenis']) . ' berhasil dibuat. Silakan susun paket soal baru di bawah ini.');
        }

        return redirect()->route('guru.ujian.index')
            ->with('success', 'Ujian ' . ucfirst($validated['jenis']) . ' berhasil dibuat untuk ' . count($siswaIdsTerpilih) . ' siswa terpilih!');
    }

    /**
     * Helper Internal: Hitung skor satu butir soal (PG, PG Kompleks, atau Essay).
     */
    private function hitungSkorSatuSoal(Soal $soal, ?Menjawab $jawab): array
    {
        $bobotSoal = (float) ($soal->bobot ?? 0);

        // Jika soal berstatus "Tidak Valid" (bobot 0), soal dianulir dan tidak mempengaruhi nilai
        if ($bobotSoal <= 0 || (!is_null($soal->kesulitan) && (int) $soal->kesulitan === 0)) {
            return [
                'skor' => 0,
                'is_benar' => false,
                'dianulir' => true,
            ];
        }

        if ($soal->tipe_soal === 'essay') {
            $skor = ($jawab && !is_null($jawab->skor_essay)) ? (float) $jawab->skor_essay : 0;
            return [
                'skor' => $skor,
                'is_benar' => $skor >= ($bobotSoal * 0.5),
            ];
        }

        if (!$jawab || empty($jawab->jawaban_dipilih)) {
            return ['skor' => 0, 'is_benar' => false];
        }

        $kunci = $soal->kunci_jawaban;

        if ($soal->tipe_soal === 'multiple_choice') {
            $cleanKunci = is_array($kunci)
                ? array_map('strtolower', array_map('trim', $kunci))
                : array_filter(array_map('strtolower', array_map('trim', explode(',', (string) $kunci))));

            $jawabanArr = array_filter(array_map('strtolower', array_map('trim', explode(',', (string) $jawab->jawaban_dipilih))));
            $totalKunci = count($cleanKunci);

            if ($totalKunci > 0) {
                $benar = count(array_intersect($jawabanArr, $cleanKunci));
                $salah = count(array_diff($jawabanArr, $cleanKunci));
                $net = max(0, $benar - $salah);
                $skor = $bobotSoal * ($net / $totalKunci);
                return [
                    'skor' => $skor,
                    'is_benar' => ($benar === $totalKunci && $salah === 0),
                ];
            }
            return ['skor' => 0, 'is_benar' => false];
        }

        // Single Choice
        $kunciTunggal = is_array($kunci) ? ($kunci[0] ?? '') : (string) $kunci;
        $isBenar = strtolower(trim((string) $jawab->jawaban_dipilih)) === strtolower(trim((string) $kunciTunggal));

        return [
            'skor' => $isBenar ? $bobotSoal : 0,
            'is_benar' => $isBenar,
        ];
    }

    /**
     * Helper Internal: Rekalkulasi Nilai Akhir Siswa & Terapkan Kebijakan Remidi ke Ujian Induk.
     */
    private function rekalkulasiNilaiSiswa(int $ujianId, int $siswaId): array
    {
        $ujian = Ujian::with('soals')->findOrFail($ujianId);
        $sesi = SeleksiUjian::where('ujian_id', $ujianId)->where('siswa_id', $siswaId)->firstOrFail();

        $soals = $ujian->soals;
        $jawabanMap = Menjawab::where('siswa_id', $siswaId)
            ->whereIn('soal_id', $soals->pluck('id'))
            ->get()
            ->keyBy('soal_id');

        $totalBobot = $soals->sum('bobot') ?: 100;
        $totalSkor = 0;

        foreach ($soals as $s) {
            $totalSkor += $this->hitungSkorSatuSoal($s, $jawabanMap->get($s->id))['skor'];
        }

        $nilaiUjianIni = round(($totalSkor / $totalBobot) * 100, 2);
        if ($nilaiUjianIni > 100)
            $nilaiUjianIni = 100;

        $sesi->update([
            'nilai_awal' => $nilaiUjianIni,
            'nilai_akhir' => $nilaiUjianIni,
        ]);

        Nilai::updateOrCreate(
            ['seleksi_ujian_id' => $sesi->id, 'siswa_id' => $siswaId],
            ['nilai' => $nilaiUjianIni, 'status' => 'selesai']
        );

        // Jika ujian ini adalah REMIDI atau SUSULAN, sinkronkan nilai ke UJIAN INDUK
        if ($ujian->parent_ujian_id && in_array($ujian->jenis_ujian, ['remidi', 'susulan'])) {
            $ujianInduk = Ujian::find($ujian->parent_ujian_id);
            if ($ujianInduk) {
                $sesiInduk = SeleksiUjian::firstOrCreate(
                    ['ujian_id' => $ujianInduk->id, 'siswa_id' => $siswaId],
                    ['nilai_awal' => $nilaiUjianIni, 'nilai_akhir' => $nilaiUjianIni, 'jumlah_remidi' => 0, 'status' => 'selesai', 'waktu_selesai' => now()]
                );

                $kkmInduk = (float) ($ujianInduk->kkm ?? 75);
                $nilaiAwalInduk = (float) $sesiInduk->nilai_awal;

                if ($ujian->jenis_ujian === 'susulan') {
                    $nilaiFinalInduk = $nilaiUjianIni;
                    $sesiInduk->update([
                        'nilai_awal' => $nilaiFinalInduk,
                        'nilai_akhir' => $nilaiFinalInduk,
                        'status' => 'selesai',
                        'waktu_selesai' => $sesiInduk->waktu_selesai ?? now(),
                    ]);
                } else {
                    // Terapkan 3 Kebijakan Nilai Remidi
                    $kebijakan = $ujian->kebijakan_remidi ?? 'kkm';
                    if ($kebijakan === 'terbaik') {
                        $nilaiFinalInduk = max($nilaiAwalInduk, $nilaiUjianIni);
                    } elseif ($kebijakan === 'rata_rata') {
                        $nilaiFinalInduk = round(($nilaiAwalInduk + $nilaiUjianIni) / 2, 2);
                    } else {
                        // 'kkm': Maksimal sebesar KKM (tidak menurunkan nilai awal jika remidi lebih rendah)
                        $nilaiFinalInduk = min($kkmInduk, max($nilaiAwalInduk, $nilaiUjianIni));
                    }

                    $sesiInduk->update([
                        'nilai_akhir' => $nilaiFinalInduk,
                        'jumlah_remidi' => max(1, (int) $sesiInduk->jumlah_remidi),
                    ]);
                }

                Nilai::updateOrCreate(
                    ['seleksi_ujian_id' => $sesiInduk->id, 'siswa_id' => $siswaId],
                    ['nilai' => $nilaiFinalInduk, 'status' => 'selesai']
                );
            }
        }

        $kkm = (float) ($ujian->kkm ?? 75);
        return [
            'nilai_akhir' => $nilaiUjianIni,
            'status_kkm' => $nilaiUjianIni >= $kkm ? 'Tuntas' : 'Remidi',
        ];
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
     * Normalisasi kunci jawaban pilihan menjadi huruf kecil a-f.++
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
