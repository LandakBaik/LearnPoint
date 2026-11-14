<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Tugas;
use App\Models\Soal;
use App\Models\Menjawab;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    // 1. Menampilkan daftar tugas guru
    public function index(Request $request)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $tugases = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals'
        ])
            ->whereHas('pengampuKelas.guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })
            ->withCount([
                'nilais as pengumpulans_count',
                'nilais as pengumpulans_dinilai_count' => function ($query) {
                    $query->whereNotNull('nilai')
                        ->where('status', 'selesai');
                },
            ])
            ->latest()
            ->get();

        // Mengambil kelas dan mata pelajaran yang diampu guru.
        $pengampuKelases = PengampuKelas::with([
            'guruMapel.mapel',
            'kelas'
        ])
            ->whereHas('guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })
            ->get();

        $kelases = $pengampuKelases->pluck('kelas')
            ->unique('id')
            ->filter();

        $mapels = $pengampuKelases->pluck('guruMapel.mapel')
            ->unique('id')
            ->filter();

        // Statistik tugas.
        $totalTugas = $tugases->count();

        $tugasAktif = $tugases->filter(function ($item) {
            return $item->deadline &&
                Carbon::parse($item->deadline)->isFuture();
        });

        $totalAktif = $tugasAktif->count();
        $totalSelesai = $totalTugas - $totalAktif;

        $perluDinilai = Nilai::whereNull('nilai')
            ->whereHas('tugas.pengampuKelas.guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })
            ->count();

        $deadlineTerdekat = $tugasAktif->filter(function ($item) {
            return Carbon::parse($item->deadline)
                ->lessThanOrEqualTo(now()->addHours(24));
        })->count();

        return view('guru.kelola_tugas.index', compact(
            'tugases',
            'pengampuKelases',
            'kelases',
            'mapels',
            'totalTugas',
            'totalAktif',
            'totalSelesai',
            'perluDinilai',
            'deadlineTerdekat'
        ));
    }

    // 2. Validasi data tugas dan soal
    private function validasiTugas(Request $request)
    {
        return $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'tipe' => 'required|in:upload,pilihan_ganda',

            // Checkbox tetap dapat memilih beberapa kelas.
            'pengampu_kelas_id' => 'required|array|min:1',
            'pengampu_kelas_id.*' => 'required|integer|distinct|exists:pengampu_kelas,id',

            'file_tugas_input' => [
                'nullable',
                'file',
                'mimes:pdf,docx,jpg,jpeg,png',
                'max:10240'
            ],

            'soal' => 'required_if:tipe,pilihan_ganda|array|min:1',
            'soal.*.pertanyaan' => 'required_if:tipe,pilihan_ganda|string',

            'soal.*.pilihan' => [
                'required_if:tipe,pilihan_ganda',
                'array',
                function ($attribute, $value, $fail) {
                    if (!is_array($value)) {
                        $fail('Pilihan jawaban harus berupa daftar pilihan.');
                        return;
                    }

                    $jumlahPilihan = collect($value)
                        ->filter(
                            fn($jawaban) =>
                            is_string($jawaban) && trim($jawaban) !== ''
                        )
                        ->count();

                    if ($jumlahPilihan < 2) {
                        $fail('Minimal harus mengisi 2 pilihan jawaban.');
                    }

                    if ($jumlahPilihan > 5) {
                        $fail('Maksimal hanya boleh mengisi 5 pilihan jawaban.');
                    }
                },
            ],

            'soal.*.pilihan.a' => 'nullable|string',
            'soal.*.pilihan.b' => 'nullable|string',
            'soal.*.pilihan.c' => 'nullable|string',
            'soal.*.pilihan.d' => 'nullable|string',
            'soal.*.pilihan.e' => 'nullable|string',

            'soal.*.kunci_jawaban' => [
                'required_if:tipe,pilihan_ganda',
                'in:a,b,c,d,e',
                function ($attribute, $value, $fail) use ($request) {
                    preg_match('/soal\.(\d+)\./', $attribute, $matches);

                    $index = $matches[1] ?? null;
                    $pilihan = $request->input("soal.$index.pilihan", []);

                    $jawabanBenar = is_array($pilihan) && is_string($value)
                        ? ($pilihan[$value] ?? null)
                        : null;

                    if (!is_string($jawabanBenar) || trim($jawabanBenar) === '') {
                        $fail('Kunci jawaban harus sesuai dengan pilihan yang sudah diisi.');
                    }
                },
            ],
        ], [
            'judul.required' => 'Judul tugas wajib diisi.',
            'deadline.required' => 'Deadline tugas wajib diisi.',
            'deadline.after' => 'Deadline harus setelah waktu sekarang.',
            'tipe.required' => 'Jenis tugas wajib dipilih.',
            'pengampu_kelas_id.required' => 'Pilih minimal satu kelas.',
            'pengampu_kelas_id.min' => 'Pilih minimal satu kelas.',
            'pengampu_kelas_id.*.exists' => 'Kelas yang dipilih tidak ditemukan.',
            'soal.required_if' => 'Minimal harus ada satu soal pilihan ganda.',
            'soal.*.pertanyaan.required_if' => 'Pertanyaan wajib diisi.',
            'soal.*.kunci_jawaban.required_if' => 'Kunci jawaban wajib dipilih.',
        ]);
    }

    // 3. Menyimpan soal pilihan ganda
    private function simpanSoal(Tugas $tugas, Request $request)
    {
        Soal::where('tugas_id', $tugas->id)->delete();

        if ($tugas->tipe !== 'pilihan_ganda') {
            return;
        }

        $daftarSoal = $request->input('soal', []);
        $totalSoal = count($daftarSoal);

        if ($totalSoal === 0) {
            return;
        }

        $bobotPerSoal = 100 / $totalSoal;

        foreach ($daftarSoal as $index => $item) {
            $pilihan = $item['pilihan'] ?? [];
            $dataPilihan = [];

            foreach (['a', 'b', 'c', 'd', 'e'] as $huruf) {
                $jawaban = $pilihan[$huruf] ?? null;

                $dataPilihan[$huruf] = is_string($jawaban) &&
                    trim($jawaban) !== ''
                    ? trim($jawaban)
                    : null;
            }

            Soal::create([
                'pertanyaan' => $item['pertanyaan'],
                'tipe_soal' => 'single_choice',
                'pilihan' => $dataPilihan,
                'kunci_jawaban' => strtolower($item['kunci_jawaban']),
                'bobot' => $bobotPerSoal,
                'urutan' => $index + 1,
                'quiz_id' => null,
                'tugas_id' => $tugas->id,
                'ujian_id' => null,
            ]);
        }
    }

    // 4. Membuat tugas baru
    public function store(Request $request)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $data = $this->validasiTugas($request);

        // Memastikan semua kelas yang dipilih merupakan kelas guru.
        $pengampus = PengampuKelas::with('guruMapel.mapel')
            ->whereIn('id', $data['pengampu_kelas_id'])
            ->whereHas('guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })
            ->get();

        if ($pengampus->count() !== count($data['pengampu_kelas_id'])) {
            return back()->withInput()->withErrors([
                'pengampu_kelas_id' => 'Ada kelas yang bukan wewenang Anda.'
            ]);
        }

        // Semua kelas harus berasal dari mata pelajaran yang sama.
        if ($pengampus->pluck('guru_mapel_id')->unique()->count() > 1) {
            return back()->withInput()->withErrors([
                'pengampu_kelas_id' => 'Pilih kelas untuk mata pelajaran yang sama.'
            ]);
        }

        $filePath = null;

        if ($data['tipe'] === 'upload' && $request->hasFile('file_tugas_input')) {
            $filePath = $request->file('file_tugas_input')
                ->store('lampiran_tugas', 'public');
        }

        try {
            // Membuat satu tugas untuk setiap kelas yang dipilih.
            $hasilTugas = DB::transaction(function () use (
                $data,
                $request,
                $filePath,
                $pengampus
            ) {
                $hasil = collect();

                foreach ($pengampus as $pengampu) {
                    $tugas = Tugas::create([
                        'pengampu_kelas_id' => $pengampu->id,
                        'judul' => $data['judul'],
                        'deskripsi' => $data['deskripsi'] ?? null,
                        'deadline' => Carbon::parse($data['deadline']),
                        'tipe' => $data['tipe'],
                        'file_lampiran' => $filePath,
                    ]);

                    // Setiap tugas memiliki soal masing-masing.
                    $this->simpanSoal($tugas, $request);

                    $hasil->push([
                        'tugas' => $tugas,
                        'pengampu' => $pengampu,
                    ]);
                }

                return $hasil;
            });
        } catch (\Throwable $e) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }

            throw $e;
        }

        $deadlineFormatted = Carbon::parse($data['deadline'])
            ->locale('id')
            ->isoFormat('D MMMM Y, HH:mm');

        // Mengirim notifikasi dengan ID tugas masing-masing kelas.
        foreach ($hasilTugas as $item) {
            $tugas = $item['tugas'];
            $pengampu = $item['pengampu'];

            $mapelNama = $pengampu->guruMapel?->mapel?->nama_mapel
                ?? 'Mata Pelajaran';

            Notifikasi::kirimKeKelas(
                $pengampu->kelas_id,
                "Tugas Baru: {$tugas->judul}",
                "Guru telah menerbitkan tugas baru untuk mata pelajaran {$mapelNama}. Batas pengumpulan: {$deadlineFormatted} WIB.",
                'tugas_baru',
                route('siswa.tugas.show', $tugas->id),
                $tugas->id
            );
        }

        return redirect()->back()
            ->with(
                'success',
                'Tugas berhasil dipublikasikan ke ' . $hasilTugas->count() . ' kelas!'
            );
    }

    // 5. Menampilkan detail pengumpulan tugas
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals'
        ])->findOrFail($id);

        abort_unless(
            $tugas->pengampuKelas?->guruMapel?->guru_id == $guru_id,
            403,
            'Akses ditolak.'
        );

        $kelas = $tugas->pengampuKelas?->kelas;

        abort_unless($kelas, 404, 'Kelas tugas tidak ditemukan.');

        // Mengambil siswa dari kelas yang menerima tugas.
        $siswas = $kelas->siswas()
            ->wherePivot('tahun_ajaran', '2026/2027')
            ->wherePivot('semester', 'ganjil')
            ->orderBy('siswas.nama_siswa')
            ->get();

        $pengumpulans = Nilai::with('siswa')
            ->where('tugas_id', $tugas->id)
            ->orderByDesc('id')
            ->get();

        $pengumpulanBySiswa = $pengumpulans
            ->unique('siswa_id')
            ->keyBy('siswa_id');

        $deadline = $tugas->deadline
            ? Carbon::parse($tugas->deadline)
            : null;

        $daftarSiswa = $siswas->map(function ($siswa) use (
            $pengumpulanBySiswa,
            $deadline
        ) {
            $pengumpulan = $pengumpulanBySiswa->get($siswa->id);

            $waktuKumpul = $pengumpulan?->created_at
                ? Carbon::parse($pengumpulan->created_at)
                : null;

            $terlambat = $waktuKumpul && $deadline
                ? $waktuKumpul->greaterThan($deadline)
                : false;

            if (!$pengumpulan) {
                $statusFilter = 'belum_kumpul';
            } elseif ($pengumpulan->nilai !== null) {
                $statusFilter = 'sudah_dinilai';
            } else {
                $statusFilter = 'perlu_diperiksa';
            }

            return [
                'siswa_id' => $siswa->id,
                'nama' => $siswa->nama_siswa,
                'nis' => $siswa->nis,
                'pengumpulan_id' => $pengumpulan?->id,
                'pengumpulan' => $pengumpulan,

                'waktu' => $waktuKumpul
                    ? $waktuKumpul->locale('id')->translatedFormat('d M Y, H:i')
                    : null,

                'terlambat' => (bool) $terlambat,
                'file' => $pengumpulan?->file_jawaban,

                'file_url' => $pengumpulan && $pengumpulan->file_jawaban
                    ? asset('storage/' . $pengumpulan->file_jawaban)
                    : null,

                'nilai' => $pengumpulan?->nilai,
                'status_filter' => $statusFilter,
            ];
        })->values();

        $halaman = $tugas->tipe === 'pilihan_ganda'
            ? 'guru.kelola_tugas.show-pilihan-ganda'
            : 'guru.kelola_tugas.show';

        return view($halaman, compact(
            'tugas',
            'pengumpulans',
            'daftarSiswa'
        ));
    }

    // 6. Menampilkan kunci jawaban pilihan ganda
    public function kunciJawaban(Request $request, string $id)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals'
        ])->findOrFail($id);

        abort_unless(
            $tugas->pengampuKelas?->guruMapel?->guru_id == $guru_id,
            403,
            'Akses ditolak.'
        );

        abort_unless($tugas->tipe === 'pilihan_ganda', 404);

        $soals = $tugas->soals
            ->sortBy('urutan')
            ->values();

        return view('guru.kelola_tugas.kunci-jawaban', compact(
            'tugas',
            'soals'
        ));
    }

    // 7. Menampilkan detail jawaban siswa
    public function detailJawabanSiswa(
        Request $request,
        string $id,
        string $pengumpulanId
    ) {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        // Mengambil tugas dan memastikan tugas milik guru.
        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals'
        ])->findOrFail($id);

        abort_unless(
            $tugas->pengampuKelas?->guruMapel?->guru_id == $guru_id,
            403,
            'Akses ditolak.'
        );

        // Mengambil data pengumpulan siswa.
        $pengumpulan = Nilai::with([
            'siswa',
            'tugas'
        ])
            ->where('tugas_id', $tugas->id)
            ->findOrFail($pengumpulanId);

        // Mengambil identitas siswa.
        $siswa = $pengumpulan->siswa;

        // Mengambil seluruh soal tugas.
        $soals = $tugas->soals
            ->sortBy('urutan')
            ->values();

        // Mengambil jawaban siswa berdasarkan ID siswa dan soal.
        $jawabans = Menjawab::where('siswa_id', $pengumpulan->siswa_id)
            ->whereIn('soal_id', $soals->pluck('id'))
            ->get()
            ->keyBy('soal_id');

        return view(
            'guru.kelola_tugas.detail-jawaban-siswa',
            compact(
                'tugas',
                'pengumpulan',
                'siswa',
                'soals',
                'jawabans'
            )
        );
    }

    // 8. Memperbarui tugas
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $tugas = Tugas::with('pengampuKelas.guruMapel')
            ->findOrFail($id);

        abort_unless(
            $tugas->pengampuKelas?->guruMapel?->guru_id == $guru_id,
            403,
            'Akses ditolak.'
        );

        $data = $this->validasiTugas($request);

        // Saat edit, satu tugas hanya boleh memiliki satu kelas.
        if (count($data['pengampu_kelas_id']) !== 1) {
            return back()->withInput()->withErrors([
                'pengampu_kelas_id' => 'Pilih tepat satu kelas untuk tugas ini.'
            ]);
        }

        $pengampuKelasId = $data['pengampu_kelas_id'][0];

        $pengampu = PengampuKelas::where('id', $pengampuKelasId)
            ->whereHas('guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })
            ->first();

        if (!$pengampu) {
            return back()->withInput()->withErrors([
                'pengampu_kelas_id' => 'Kelas atau mata pelajaran bukan wewenang Anda.'
            ]);
        }

        $fileLama = $tugas->file_lampiran;
        $fileBaru = null;

        if (
            $data['tipe'] === 'upload' &&
            $request->hasFile('file_tugas_input')
        ) {
            $fileBaru = $request->file('file_tugas_input')
                ->store('lampiran_tugas', 'public');
        }

        $filePath = $fileBaru
            ?? ($data['tipe'] === 'upload' ? $fileLama : null);

        try {
            DB::transaction(function () use (
                $data,
                $request,
                $tugas,
                $filePath,
                $pengampuKelasId
            ) {
                $tugas->update([
                    'judul' => $data['judul'],
                    'deskripsi' => $data['deskripsi'] ?? null,
                    'deadline' => Carbon::parse($data['deadline']),
                    'tipe' => $data['tipe'],
                    'file_lampiran' => $filePath,
                    'pengampu_kelas_id' => $pengampuKelasId,
                ]);

                $this->simpanSoal($tugas, $request);
            });
        } catch (\Throwable $e) {
            if ($fileBaru) {
                Storage::disk('public')->delete($fileBaru);
            }

            throw $e;
        }

        // Hapus file lama hanya jika tidak dipakai tugas lain.
        if (
            $fileLama &&
            $fileLama !== $filePath &&
            !Tugas::where('file_lampiran', $fileLama)->exists() &&
            Storage::disk('public')->exists($fileLama)
        ) {
            Storage::disk('public')->delete($fileLama);
        }

        return redirect()->back()
            ->with('success', 'Tugas berhasil diperbarui!');
    }

    // 9. Menghapus tugas
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $tugas = Tugas::findOrFail($id);

        abort_unless(
            $tugas->pengampuKelas?->guruMapel?->guru_id == $guru_id,
            403,
            'Akses ditolak.'
        );

        $fileLama = $tugas->file_lampiran;

        DB::transaction(function () use ($tugas) {
            Soal::where('tugas_id', $tugas->id)->delete();

            $tugas->delete();
        });

        // Jangan hapus file jika masih digunakan tugas kelas lain.
        if (
            $fileLama &&
            !Tugas::where('file_lampiran', $fileLama)->exists() &&
            Storage::disk('public')->exists($fileLama)
        ) {
            Storage::disk('public')->delete($fileLama);
        }

        return redirect()->route('guru.tugas')
            ->with('success', 'Tugas berhasil dihapus!');
    }

    // 10. Memberikan nilai kepada siswa
    public function berikanNilai(Request $request, string $id)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $data = $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
        ]);

        $pengumpulan = Nilai::with([
            'tugas.pengampuKelas.guruMapel',
            'siswa.user'
        ])->findOrFail($id);

        $milikGuru = $pengumpulan->tugas
            ?->pengampuKelas
            ?->guruMapel
            ?->guru_id == $guru_id;

        abort_unless($milikGuru, 403, 'Akses ditolak.');

        $pengumpulan->update([
            'nilai' => $data['nilai'],
            'status' => 'selesai',
        ]);

        if ($pengumpulan->siswa?->user) {
            $tugasJudul = $pengumpulan->tugas?->judul ?? 'Tugas';

            Notifikasi::kirim(
                $pengumpulan->siswa->user->id,
                "Nilai Tugas: {$tugasJudul}",
                "Guru telah memberikan nilai {$data['nilai']} untuk tugas '{$tugasJudul}'. Silakan cek detail evaluasi Anda.",
                'nilai_tugas',
                route('siswa.tugas.show', $pengumpulan->tugas_id),
                $pengumpulan->tugas_id,
                [
                    'nilai' => $data['nilai'],
                    'tugas_id' => $pengumpulan->tugas_id
                ]
            );
        }

        return redirect()->back()
            ->with('success', 'Nilai siswa berhasil disimpan!');
    }
}
