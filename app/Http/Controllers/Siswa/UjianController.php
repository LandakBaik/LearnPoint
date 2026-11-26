<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\SeleksiUjian;
use App\Models\Soal;
use App\Models\Ujian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UjianController extends Controller
{
    /**
     * Memastikan ujian milik kelas siswa (serta sesuai agama dan daftar peserta khusus jika remidi/susulan).
     */
    private function authorizeUjianSiswa(Ujian $ujian, $siswa): void
    {
        abort_unless($siswa, 403, 'Profil siswa tidak ditemukan.');

        $ujian->loadMissing(['pengampuKelas.guruMapel.mapel']);

        $kelas = $siswa->kelas;
        $kelasId = $kelas?->id;

        abort_unless(
            $kelasId && (int) $ujian->pengampuKelas?->kelas_id === (int) $kelasId,
            403,
            'Anda tidak memiliki akses ke ujian kelas lain.'
        );

        $mapel = $ujian->pengampuKelas?->guruMapel?->mapel;
        if ($mapel && method_exists($mapel, 'isAccessibleBySiswa')) {
            abort_unless(
                $mapel->isAccessibleBySiswa($siswa),
                403,
                'Mata pelajaran ujian ini tidak sesuai dengan agama Anda.'
            );
        }

        if (in_array($ujian->jenis_ujian, ['remidi', 'susulan']) && !empty($ujian->peserta_khusus)) {
            abort_unless(
                in_array((int) $siswa->id, array_map('intval', $ujian->peserta_khusus)),
                403,
                'Anda tidak terdaftar sebagai peserta pada ujian remidi/susulan ini.'
            );
        }
    }

    /**
     * Menampilkan daftar semua ujian untuk siswa.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $kelas = $siswa?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId) {
            $ujians = collect();
        } else {
            // Ambil ujian yang ditujukan untuk kelas siswa
            $query = Ujian::with([
                'pengampuKelas.guruMapel.guru',
                'pengampuKelas.guruMapel.mapel',
                'pengampuKelas.kelas',
                'seleksiUjians' => function ($q) use ($siswa) {
                    if ($siswa) {
                        $q->where('siswa_id', $siswa->id);
                    }
                },
                'seleksiUjians.nilais'
            ])
                ->whereIn('status', ['scheduled', 'published', 'completed']);

            $query->whereHas('pengampuKelas', function ($q) use ($kelasId, $siswa) {
                $q->forSiswa($siswa)->where('kelas_id', $kelasId);
            });

            $ujians = $query->latest('waktu_mulai')->get()
             ->filter(function ($ujian) use ($siswa) {
                 if (in_array($ujian->jenis_ujian, ['remidi', 'susulan']) && !empty($ujian->peserta_khusus)) {
                     return $siswa && in_array((int) $siswa->id, array_map('intval', $ujian->peserta_khusus));
                 }
                 return true;
             })
             ->values()
             ->map(function ($ujian) {
                 $sesi = $ujian->seleksiUjians->first();
                 $nilai = $sesi?->nilais?->first();

                 $ujian->sudah_dikerjakan = $nilai !== null;
                 $ujian->nilai_siswa = $nilai?->nilai;
                 return $ujian;
             });
        }

        // Hitung statistik ringkas untuk header tab
        $totalUjian = $ujians->count();
        $totalSelesai = $ujians->where('sudah_dikerjakan', true)->count();
        $totalBelum = $totalUjian - $totalSelesai;

        return view('siswa.ujian.index', compact(
            'ujians',
            'kelas',
            'totalUjian',
            'totalSelesai',
            'totalBelum'
        ));
    }

    /**
     * Menampilkan detail informasi dan kisi-kisi ujian untuk siswa.
     */
    public function show(Ujian $ujian)
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $this->authorizeUjianSiswa($ujian, $siswa);
        $siswaId = $siswa->id;

        // Sinkronisasi status jadwal jika method tersedia di Model Ujian
        if (method_exists($ujian, 'sinkronisasiStatus')) {
            $ujian->sinkronisasiStatus();
        }

        abort_unless(
            in_array($ujian->status, ['scheduled', 'published', 'completed']),
            403,
            'Ujian ini belum tersedia untuk siswa.'
        );

        $ujian->load([
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
            'seleksiUjians' => function ($q) use ($siswaId) {
                $q->where('siswa_id', $siswaId);
            },
            'seleksiUjians.nilais'
        ]);

        $sesi = $ujian->seleksiUjians->first();
        $nilai = $sesi?->nilais?->first();
        $sudahDikerjakan = $nilai !== null;

        // Hitung ringkasan tipe soal
        $ringkasanTipe = [
            'single_choice' => $ujian->soals->where('tipe_soal', 'single_choice')->count(),
            'multiple_choice' => $ujian->soals->where('tipe_soal', 'multiple_choice')->count(),
            'essay' => $ujian->soals->where('tipe_soal', 'essay')->count(),
            'matching' => $ujian->soals->where('tipe_soal', 'matching')->count(),
            'total_poin' => $ujian->soals->sum('bobot') ?: 100,
        ];

        return view('siswa.ujian.show', compact('ujian', 'sudahDikerjakan', 'nilai', 'ringkasanTipe'));
    }

    /**
     * Menampilkan halaman konfirmasi & input token sebelum masuk ke lembar soal.
     */
    public function konfirmasi(Ujian $ujian)
    {
        $user = auth()->user();
        $siswa = $user->siswa;
        $this->authorizeUjianSiswa($ujian, $siswa);
        $siswaId = $siswa->id;

        if ($ujian->status !== 'published') {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Ujian ini belum aktif atau sedang ditarik oleh pengawas.');
        }

        // Cek apakah siswa sudah menyelesaikan ujian ini
        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->first();

        if ($sesi && ($sesi->status === 'selesai' || !is_null($sesi->waktu_selesai))) {
            return redirect()->route('siswa.ujian.hasil', $ujian->id)
                ->with('info', 'Anda telah menyelesaikan ujian ini.');
        }

        // Jika siswa sedang mengerjakan dan token di session browser sudah valid, langsung lanjutkan
        if ($sesi && session('token_ujian_verified_' . $ujian->id) === $ujian->token) {
            return redirect()->route('siswa.ujian.kerjakan', $ujian->id);
        }

        $waktuMulaiSesi = Carbon::parse($ujian->waktu_mulai_sesi ?? $ujian->waktu_mulai ?? now());
        $batasAkhirSesi = $waktuMulaiSesi->copy()->addMinutes((int) $ujian->durasi_menit);

        return view('siswa.ujian.konfirmasi', compact('ujian', 'siswa', 'waktuMulaiSesi', 'batasAkhirSesi'));
    }

    /**
     * Memvalidasi Token Sesi & Jadwal Sesi (Double Lock) sebelum membuka lembar soal.
     */
    public function validasiToken(Request $request, Ujian $ujian)
    {
        $request->validate([
            'token' => 'required|string',
        ], [
            'token.required' => 'Token ujian wajib diisi.',
        ]);

        $user = auth()->user();
        $siswa = $user->siswa;
        $this->authorizeUjianSiswa($ujian, $siswa);
        $siswaId = $siswa->id;

        if ($ujian->status !== 'published') {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Ujian ini sedang tidak aktif.');
        }

        $inputToken = strtoupper(trim($request->token));
        $tokenKelas = strtoupper(trim((string) $ujian->token));
        $nomorSesiKelas = $ujian->sesi ?? 1;

        // =========================================================================
        // PENGAMAN 1: VALIDASI TOKEN & DETEKSI JIKA MEMASUKKAN TOKEN SESI LAIN
        // =========================================================================
        if (empty($tokenKelas) || $inputToken !== $tokenKelas) {
            if ($ujian->id_grup_ujian) {
                $ujianSesiLain = Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)
                    ->whereRaw('UPPER(token) = ?', [$inputToken])
                    ->first();

                if ($ujianSesiLain) {
                    return back()->withInput()->with(
                        'error',
                        "Token tersebut milik Sesi {$ujianSesiLain->sesi}. Kelas Anda terdaftar pada Sesi {$nomorSesiKelas}."
                    );
                }
            }

            return back()->withInput()->with(
                'error',
                'Token ujian tidak valid atau belum dirilis oleh guru pengawas.'
            );
        }

        // =========================================================================
        // PENGAMAN 2: VALIDASI JADWAL WAKTU MULAI & BATAS AKHIR SESI
        // =========================================================================
        $waktuMulaiSesi = Carbon::parse($ujian->waktu_mulai_sesi ?? $ujian->waktu_mulai ?? now());
        $batasAkhirSesi = $waktuMulaiSesi->copy()->addMinutes((int) $ujian->durasi_menit);

        if (now()->lt($waktuMulaiSesi)) {
            return back()->withInput()->with(
                'error',
                "Sesi {$nomorSesiKelas} belum dimulai. Jadwal sesi Anda dibuka pada pukul " . $waktuMulaiSesi->format('H:i') . " WIB."
            );
        }

        if (now()->greaterThanOrEqualTo($batasAkhirSesi)) {
            return back()->withInput()->with(
                'error',
                "Waktu pengerjaan untuk Sesi {$nomorSesiKelas} telah berakhir pada pukul " . $batasAkhirSesi->format('H:i') . " WIB."
            );
        }

        // Simpan tanda kelolosan token di session
        session(['token_ujian_verified_' . $ujian->id => $ujian->token]);

        // Buat atau ambil sesi pengerjaan siswa
        $sesi = SeleksiUjian::firstOrCreate(
            [
                'ujian_id' => $ujian->id,
                'siswa_id' => $siswaId,
            ],
            [
                'nilai_awal'        => 0,
                'nilai_akhir'       => 0,
                'jumlah_remidi'     => 0,
                'waktu_mulai'       => now(),
                'status'            => 'sedang_mengerjakan',
                'pelanggaran_count' => 0,
            ]
        );

        if (!$sesi->waktu_mulai) {
            $sesi->update(['waktu_mulai' => now(), 'status' => 'sedang_mengerjakan']);
        }

        return redirect()->route('siswa.ujian.kerjakan', $ujian->id);
    }

    /**
     * Menampilkan ruang ujian interaktif dengan Timer Sesi Terpusat.
     */
    public function kerjakan(Ujian $ujian)
    {
        $user = auth()->user();
        $siswa = $user->siswa;
        $this->authorizeUjianSiswa($ujian, $siswa);
        $siswaId = $siswa->id;

        if ($ujian->status !== 'published') {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Ujian ini sedang ditarik dari publikasi oleh guru pengawas.');
        }

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->first();

        // Jika sudah selesai, langsung arahkan ke hasil
        if ($sesi && ($sesi->status === 'selesai' || !is_null($sesi->waktu_selesai))) {
            return redirect()->route('siswa.ujian.hasil', $ujian->id)
                ->with('info', 'Ujian Anda telah selesai.');
        }

        // Wajib melewati halaman input token terlebih dahulu
        if (!$sesi || session('token_ujian_verified_' . $ujian->id) !== $ujian->token) {
            return redirect()->route('siswa.ujian.konfirmasi', $ujian->id)
                ->with('error', 'Silakan masukkan token sesi ujian terlebih dahulu.');
        }

        // =========================================================================
        // TIMER SESI TERPUSAT:
        // Dihitung dari (waktu_mulai_sesi + durasi_menit) dikurangi waktu sekarang.
        // =========================================================================
        $waktuMulaiSesi = Carbon::parse($ujian->waktu_mulai_sesi ?? $ujian->waktu_mulai ?? $sesi->waktu_mulai ?? now());
        $batasAkhirSesi = $waktuMulaiSesi->copy()->addMinutes((int) $ujian->durasi_menit);
        $sisaDetik = max(0, (int) now()->diffInSeconds($batasAkhirSesi, false));

        if ($sisaDetik <= 0) {
            return $this->submit(request(), $ujian);
        }

        // Urutkan soal sesuai instrumen guru
        $soals = $ujian->soals()->orderBy('urutan', 'asc')->orderBy('id', 'asc')->get();

        $soalsData = $soals->map(function ($soal, $index) {
            $pilihan = is_array($soal->pilihan) ? $soal->pilihan : json_decode($soal->pilihan, true) ?? [];
            $opsiFormatted = [];
            foreach ($pilihan as $k => $v) {
                $kode = is_numeric($k) ? chr(97 + (int)$k) : strtolower($k);
                $opsiFormatted[] = [
                    'kode'  => $kode,
                    'label' => strtoupper($kode),
                    'teks'  => $v,
                ];
            }

            // Normalisasi path gambar agar tidak terjadi double 'storage/' atau URL rusak
            $gambarUrl = null;
            if (!empty($soal->gambar)) {
                $pathBersih = ltrim((string) $soal->gambar, '/');
                if (str_starts_with($pathBersih, 'http://') || str_starts_with($pathBersih, 'https://')) {
                    $gambarUrl = $pathBersih;
                } elseif (str_starts_with($pathBersih, 'storage/')) {
                    $gambarUrl = asset($pathBersih);
                } else {
                    $gambarUrl = asset('storage/' . $pathBersih);
                }
            }

            return [
                'id'         => $soal->id,
                'nomor'      => $index + 1,
                'pertanyaan' => $soal->pertanyaan,
                'tipe_soal'  => $soal->tipe_soal,
                'gambar'     => $gambarUrl,
                'bobot'      => (float) ($soal->bobot ?? 1),
                'pilihan'    => $opsiFormatted,
            ];
        });

        $savedAnswers = Menjawab::where('siswa_id', $siswaId)
            ->whereIn('soal_id', $soals->pluck('id'))
            ->pluck('jawaban_dipilih', 'soal_id')
            ->toArray();

        return view('siswa.ujian.kerjakan', compact(
            'ujian',
            'sesi',
            'soalsData',
            'savedAnswers',
            'sisaDetik'
        ));
    }

    /**
     * Endpoint Heartbeat Sesi (Dipanggil berkala tiap 3 detik oleh browser siswa).
     */
    public function cekStatusSesi(Ujian $ujian)
    {
        $siswa = auth()->user()?->siswa;
        $this->authorizeUjianSiswa($ujian, $siswa);

        if ($ujian->status === 'unpublished' || $ujian->status === 'draft') {
            return response()->json([
                'status'       => 'ditarik',
                'message'      => 'Ujian telah ditarik dari publikasi oleh guru pengawas.',
                'redirect_url' => route('siswa.dashboard'),
            ]);
        }

        $siswaId = $siswa->id;

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->first();

        if (!$sesi) {
            return response()->json(['status' => 'belum_mulai']);
        }

        $waktuMulaiSesi = Carbon::parse($ujian->waktu_mulai_sesi ?? $ujian->waktu_mulai ?? $sesi->waktu_mulai ?? now());
        $batasAkhirSesi = $waktuMulaiSesi->copy()->addMinutes((int) $ujian->durasi_menit);
        $sisaDetik = max(0, (int) now()->diffInSeconds($batasAkhirSesi, false));

        return response()->json([
            'status'            => $sesi->status,
            'pelanggaran_count' => (int) ($sesi->pelanggaran_count ?? 0),
            'sisa_detik'        => $sisaDetik,
            'is_timeout'        => ($sisaDetik <= 0 && $sesi->status !== 'selesai'),
            'redirect_url'      => route('siswa.ujian.hasil', $ujian->id),
        ]);
    }
    /**
     * Auto-save jawaban siswa ke tabel menjawabs.
     */
    public function simpanJawaban(Request $request, Ujian $ujian)
    {
        $request->validate([
            'soal_id' => 'required|exists:soals,id',
            'jawaban' => 'nullable',
        ]);

        $user = Auth::user();
        $siswa = $user->siswa;
        $this->authorizeUjianSiswa($ujian, $siswa);
        $siswaId = $siswa->id;

        $ujian->sinkronisasiStatus();
        if ($ujian->status !== 'published') {
            return response()->json(['status' => 'error', 'message' => 'Ujian sedang tidak aktif.'], 403);
        }

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->first();

        if (!$sesi) {
            return response()->json(['status' => 'error', 'message' => 'Sesi ujian belum dimulai.'], 403);
        }

        if ($sesi->status === 'terkunci') {
            return response()->json(['status' => 'error', 'message' => 'Sesi ujian Anda sedang terkunci.'], 403);
        }

        if ($sesi->status === 'selesai') {
            return response()->json(['status' => 'error', 'message' => 'Ujian sudah diselesaikan dan jawaban tidak dapat diubah.'], 403);
        }

        $waktuMulaiSesi = Carbon::parse($ujian->waktu_mulai_sesi ?? $ujian->waktu_mulai ?? $sesi->waktu_mulai ?? now());
        $batasAkhir = $waktuMulaiSesi->copy()->addMinutes((int) $ujian->durasi_menit);
        if (now()->greaterThan($batasAkhir->copy()->addMinutes(2))) {
            return response()->json(['status' => 'error', 'message' => 'Waktu pengerjaan ujian telah berakhir.'], 403);
        }

        $soal = Soal::where('ujian_id', $ujian->id)->findOrFail($request->soal_id);

        $rawJawaban = $request->jawaban;
        if ($soal->tipe_soal === 'multiple_choice') {
            $cleanJawabanList = is_array($rawJawaban)
                ? array_values(array_unique(array_filter(array_map(fn ($v) => strtolower(trim((string) $v)), $rawJawaban))))
                : array_values(array_unique(array_filter(array_map(fn ($v) => strtolower(trim($v)), explode(',', (string) $rawJawaban)))));
            sort($cleanJawabanList);
            $jawabanString = implode(',', $cleanJawabanList);
        } else {
            $jawabanString = is_array($rawJawaban) ? implode(',', $rawJawaban) : (string) $rawJawaban;
        }

        // Cek status benar untuk keperluan indikator
        $kunci = $soal->kunci_jawaban;
        $isBenar = false;

        if ($soal->tipe_soal === 'multiple_choice') {
            $cleanKunci = is_array($kunci)
                ? array_values(array_unique(array_filter(array_map(fn ($v) => strtolower(trim((string) $v)), $kunci))))
                : array_values(array_unique(array_filter(array_map(fn ($v) => strtolower(trim($v)), explode(',', (string) $kunci)))));

            sort($cleanKunci);
            $isBenar = ($cleanKunci === $cleanJawabanList);
        } else {
            $isBenar = (strtolower(trim($jawabanString)) === strtolower(trim(is_array($kunci) ? ($kunci[0] ?? '') : (string) $kunci)));
        }

        Menjawab::updateOrCreate(
            [
                'soal_id' => $soal->id,
                'siswa_id' => $siswaId,
            ],
            [
                'jawaban_dipilih' => $jawabanString,
                'benar' => $isBenar,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Jawaban berhasil disimpan otomatis',
        ]);
    }
    /**
     * Mencatat log pelanggaran layar (anti-cheat) dan mengunci ujian jika melewati batas toleransi.
     */
    public function catatPelanggaran(Request $request, Ujian $ujian)
    {
        $siswa = auth()->user()?->siswa;
        $this->authorizeUjianSiswa($ujian, $siswa);
        $siswaId = $siswa->id;

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->first();

        if (!$sesi || in_array($sesi->status, ['selesai', 'terkunci'], true)) {
            return response()->json(['status' => 'ignored']);
        }

        $sesi->increment('pelanggaran_count');
        $sesi->refresh();

        $maxToleransi = 3;
        $isLocked = false;

        // Mulai pelanggaran ke-3, 4, 5, 6, dst. langsung terkunci otomatis
        if ($sesi->pelanggaran_count >= $maxToleransi) {
            $sesi->update(['status' => 'terkunci']);
            $isLocked = true;
        }

        return response()->json([
            'status'            => 'success',
            'pelanggaran_count' => (int) $sesi->pelanggaran_count,
            'max_toleransi'     => $maxToleransi,
            'is_locked'         => $isLocked,
        ]);
    }

    /**
     * Membuka blokir sesi ujian siswa menggunakan Kode Buka Blokir dari pengawas/admin.
     */
    public function bukaKunciDenganKode(Request $request, Ujian $ujian)
    {
        $request->validate([
            'kode' => 'required|string',
        ], [
            'kode.required' => 'Silakan masukkan kode buka blokir.',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $siswa = $user?->siswa;
        $this->authorizeUjianSiswa($ujian, $siswa);
        $siswaId = $siswa->id;

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->first();

        if (!$sesi) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sesi ujian tidak ditemukan.',
            ], 404);
        }

        if ($sesi->status !== 'terkunci') {
            return response()->json([
                'status'  => 'success',
                'message' => 'Sesi ujian sudah aktif.',
            ]);
        }

        $inputKode = strtoupper(trim($request->kode));
        $kodeAktif = strtoupper(trim((string) $ujian->unlock_code));

        if (empty($kodeAktif) || $inputKode !== $kodeAktif) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode buka blokir tidak valid atau sudah diperbarui oleh pengawas.',
            ], 422);
        }

        // Buka kunci dan reset hitungan pelanggaran
        $sesi->update([
            'status'            => 'sedang_mengerjakan',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Blokir berhasil dibuka. Silakan lanjutkan pengerjaan ujian.',
        ]);
    }



    /**
     * Submit ujian, kalkulasi skor proporsional, dan simpan nilai.
     */
    public function submit(Request $request, Ujian $ujian)
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $this->authorizeUjianSiswa($ujian, $siswa);
        $siswaId = $siswa->id;

        $ujian->sinkronisasiStatus();

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->firstOrFail();

        if ($sesi->status === 'selesai') {
            return redirect()->route('siswa.ujian.hasil', $ujian->id)
                ->with('info', 'Ujian Anda sudah dikumpulkan sebelumnya.');
        }

        if ($sesi->status === 'terkunci') {
            return redirect()->route('siswa.ujian.kerjakan', $ujian->id)
                ->with('error', 'Sesi ujian Anda sedang terkunci. Hubungi pengawas untuk membuka kunci.');
        }

        if (!in_array($ujian->status, ['published', 'completed'], true)) {
            return redirect()->route('siswa.ujian.index')
                ->with('error', 'Ujian sedang tidak aktif.');
        }

        return DB::transaction(function () use ($ujian, $siswa, $siswaId, $sesi) {
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

                // 1. PENILAIAN PILIHAN GANDA KOMPLEKS (PROPORSIONAL)
                if ($soal->tipe_soal === 'multiple_choice') {
                    $cleanKunci = is_array($kunci)
                        ? array_values(array_unique(array_filter(array_map(fn ($v) => strtolower(trim((string) $v)), $kunci))))
                        : array_values(array_unique(array_filter(array_map(fn ($v) => strtolower(trim($v)), explode(',', (string) $kunci)))));

                    $jawabanSiswaArr = array_values(array_unique(array_filter(array_map(fn ($v) => strtolower(trim($v)), explode(',', (string) $jawab->jawaban_dipilih)))));

                    $totalKunci = count($cleanKunci);
                    if ($totalKunci > 0) {
                        // Jumlah pilihan siswa yang cocok dengan kunci
                        $benarPilihan = count(array_intersect($jawabanSiswaArr, $cleanKunci));

                        // Penalti jika mencentang pilihan yang salah
                        $salahPilihan = count(array_diff($jawabanSiswaArr, $cleanKunci));

                        // Hitung skor bersih (dibatasi [0, bobotSoal])
                        $netBenar = max(0, min($totalKunci, $benarPilihan - $salahPilihan));
                        $rasio = $netBenar / $totalKunci;

                        $skorDiperoleh += min($bobotSoal, $bobotSoal * $rasio);
                    }
                }
                // 2. PENILAIAN PILIHAN GANDA BIASA (SINGLE CHOICE)
                elseif ($soal->tipe_soal === 'single_choice') {
                    $kunciTunggal = is_array($kunci) ? ($kunci[0] ?? '') : (string) $kunci;
                    if (strtolower(trim((string) $jawab->jawaban_dipilih)) === strtolower(trim((string) $kunciTunggal))) {
                        $skorDiperoleh += $bobotSoal;
                    }
                }
                // 3. ESSAY (Dinilai manual guru, skor otomatis dilewati)
            }

            // Normalisasi nilai akhir (skala 0 - 100)
            $nilaiAkhir = $totalBobot > 0 ? round(($skorDiperoleh / $totalBobot) * 100, 2) : 0;
            if ($nilaiAkhir > 100) {
                $nilaiAkhir = 100;
            }

            $waktuAwal = $sesi->waktu_mulai ?? $sesi->created_at;
            $durasiDetik = (int) Carbon::parse($waktuAwal)->diffInSeconds(now());

            // 1. Simpan ke tabel seleksi_ujians DENGAN STATUS SELESAI
            $sesi->update([
                'status' => 'selesai',
                'waktu_selesai' => now(),
                'nilai_awal' => $nilaiAkhir,
                'nilai_akhir' => $nilaiAkhir,
            ]);

            // 2. Simpan ke tabel nilais
            Nilai::updateOrCreate(
                [
                    'seleksi_ujian_id' => $sesi->id,
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

            // Pastikan semua soal essay memiliki record di menjawabs (skor_essay = null agar status Kuning)
         foreach ($soals->where('tipe_soal', 'essay') as $soalEssay) {
             Menjawab::firstOrCreate(
                 ['soal_id' => $soalEssay->id, 'siswa_id' => $siswaId],
                 ['jawaban_dipilih' => '', 'benar' => false, 'skor_essay' => null]
             );
         }

         // Jika ujian ini REMIDI / SUSULAN dan TIDAK ADA ESSAY, langsung update nilai di Ujian Induk
         $adaEssay = $soals->where('tipe_soal', 'essay')->isNotEmpty();
         if (!$adaEssay && $ujian->parent_ujian_id && in_array($ujian->jenis_ujian, ['remidi', 'susulan'])) {
             $ujianInduk = Ujian::find($ujian->parent_ujian_id);
             if ($ujianInduk) {
                 $sesiInduk = SeleksiUjian::firstOrCreate(
                     ['ujian_id' => $ujianInduk->id, 'siswa_id' => $siswaId],
                     ['nilai_awal' => $nilaiAkhir, 'nilai_akhir' => $nilaiAkhir, 'jumlah_remidi' => 0, 'status' => 'selesai', 'waktu_selesai' => now()]
                 );

                 $kkmInduk = (float) ($ujianInduk->kkm ?? 75);
                 $nilaiAwalInduk = (float) $sesiInduk->nilai_awal;

                 if ($ujian->jenis_ujian === 'susulan') {
                     $nilaiFinalInduk = $nilaiAkhir;
                     $sesiInduk->update([
                         'nilai_awal'    => $nilaiFinalInduk,
                         'nilai_akhir'   => $nilaiFinalInduk,
                         'status'        => 'selesai',
                         'waktu_selesai' => $sesiInduk->waktu_selesai ?? now(),
                     ]);
                 } else {
                     $kebijakan = $ujian->kebijakan_remidi ?? 'kkm';
                     if ($kebijakan === 'terbaik') {
                         $nilaiFinalInduk = max($nilaiAwalInduk, $nilaiAkhir);
                     } elseif ($kebijakan === 'rata_rata') {
                         $nilaiFinalInduk = round(($nilaiAwalInduk + $nilaiAkhir) / 2, 2);
                     } else {
                         $nilaiFinalInduk = min($kkmInduk, max($nilaiAwalInduk, $nilaiAkhir));
                     }

                     $sesiInduk->update([
                         'nilai_akhir'   => $nilaiFinalInduk,
                         'jumlah_remidi' => (int) $sesiInduk->jumlah_remidi + 1,
                     ]);
                 }

                 Nilai::updateOrCreate(
                     ['seleksi_ujian_id' => $sesiInduk->id, 'siswa_id' => $siswaId],
                     ['nilai' => $nilaiFinalInduk, 'status' => 'selesai']
                 );
             }
         }

            app(\App\Services\UjianNotifikasiService::class)->notifyUjianSelesaiKeGuru(
                $ujian,
                $siswa,
                (float) $nilaiAkhir,
                $adaEssay
            );

            // JIKA KELUAR DARI TOMBOL KELUAR -> ARAHKAN KE DASHBOARD
            // Ganti baris pengecekan tersebut menjadi:
            if (request('redirect_to') === 'dashboard') {
                return redirect()->route('siswa.dashboard')
                    ->with('success', 'Ujian telah dikumpulkan otomatis dan sesi Anda telah selesai.');
            }
            // DEFAULT: ARAHKAN KE HALAMAN HASIL
            return redirect()->route('siswa.ujian.hasil', $ujian->id)
                ->with('success', 'Ujian berhasil dikumpulkan!');
        });
    }
    /**
     * Menampilkan halaman rekapitulasi hasil ujian siswa.
     */
    /**
     * Menampilkan halaman ringkasan skor dan hasil pengerjaan ujian.
     */
    public function hasil(Ujian $ujian)
    {
        $siswa = auth()->user()?->siswa;
        $this->authorizeUjianSiswa($ujian, $siswa);
        $siswaId = $siswa->id;

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->firstOrFail();

        if ($sesi->status !== 'selesai') {
            return redirect()->route('siswa.ujian.kerjakan', $ujian->id)
                ->with('warning', 'Selesaikan ujian terlebih dahulu untuk melihat hasil.');
        }

        // 1. Ambil data nilai dari tabel nilais, atau fallback dari data seleksi_ujians
        $nilai = Nilai::where('seleksi_ujian_id', $sesi->id)
            ->where('siswa_id', $siswaId)
            ->first();

        // Fallback jika baris tabel nilais belum sempat terbuat
        if (!$nilai) {
            $nilai = (object) [
                'nilai' => $sesi->nilai_akhir ?? $sesi->nilai_awal ?? 0,
                'status' => $sesi->status ?? 'selesai',
            ];
        }

        // 2. Rekap butir soal dan jawaban
        $soalIds = $ujian->soals()->pluck('id');
        $totalSoal = $soalIds->count();

        $jawaban = Menjawab::where('siswa_id', $siswaId)
            ->whereIn('soal_id', $soalIds)
            ->get();

        $totalBenar = $jawaban->where('benar', 1)->count();
        $totalSalah = max(0, $totalSoal - $totalBenar);

        // 3. Sertakan $nilai ke dalam compact
        return view('siswa.ujian.hasil', compact(
            'ujian',
            'sesi',
            'nilai',
            'totalSoal',
            'totalBenar',
            'totalSalah'
        ));
    }
}
