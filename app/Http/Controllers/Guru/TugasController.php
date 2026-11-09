<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\Tugas;
use App\Models\Soal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. MENAMPILKAN DAFTAR TUGAS
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        // Pastikan pengguna merupakan guru.
        abort_unless($user?->role === 'guru' && $guru_id, 403);

        // Ambil tugas berdasarkan kelas dan mata pelajaran yang diampu guru.
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

        // Ambil daftar kelas dan mata pelajaran yang diampu guru.
        $pengampuKelases = PengampuKelas::with([
            'guruMapel.mapel',
            'kelas'
        ])
            ->whereHas('guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })
            ->get();

        // Pisahkan data kelas dan mata pelajaran untuk kebutuhan filter.
        $kelases = $pengampuKelases->pluck('kelas')
            ->unique('id')
            ->filter();

        $mapels = $pengampuKelases->pluck('guruMapel.mapel')
            ->unique('id')
            ->filter();

        // Hitung jumlah seluruh tugas.
        $totalTugas = $tugases->count();

        // Hitung tugas yang deadline-nya masih berlaku.
        $tugasAktif = $tugases->filter(function ($item) {
            return $item->deadline &&
                Carbon::parse($item->deadline)->isFuture();
        });

        $totalAktif = $tugasAktif->count();
        $totalSelesai = $totalTugas - $totalAktif;

        // Hitung pengumpulan yang belum mendapatkan nilai.
        $perluDinilai = Nilai::whereNull('nilai')
            ->whereHas('tugas.pengampuKelas.guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })
            ->count();

        // Hitung tugas yang deadline-nya kurang dari 24 jam.
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


    /*
    |--------------------------------------------------------------------------
    | 2. VALIDASI DATA TUGAS DAN SOAL
    |--------------------------------------------------------------------------
    */
    private function validasiTugas(Request $request)
    {
        return $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'tipe' => 'required|in:upload,pilihan_ganda',
            'pengampu_kelas_id' => 'required|exists:pengampu_kelas,id',

            // Validasi file lampiran tugas.
            'file_tugas_input' => [
                'nullable',
                'file',
                'mimes:pdf,docx,jpg,jpeg,png',
                'max:10240'
            ],

            // Validasi soal pilihan ganda.
            'soal' => 'required_if:tipe,pilihan_ganda|array|min:1',
            'soal.*.pertanyaan' => 'required_if:tipe,pilihan_ganda|string',

            // Setiap soal harus memiliki lima pilihan jawaban.
            'soal.*.pilihan' => 'required_if:tipe,pilihan_ganda|array|size:5',
            'soal.*.pilihan.a' => 'required_if:tipe,pilihan_ganda|string',
            'soal.*.pilihan.b' => 'required_if:tipe,pilihan_ganda|string',
            'soal.*.pilihan.c' => 'required_if:tipe,pilihan_ganda|string',
            'soal.*.pilihan.d' => 'required_if:tipe,pilihan_ganda|string',
            'soal.*.pilihan.e' => 'required_if:tipe,pilihan_ganda|string',

            // Validasi kunci jawaban.
            'soal.*.kunci_jawaban' => [
                'required_if:tipe,pilihan_ganda',
                'in:a,b,c,d,e'
            ],

            // Bobot tidak divalidasi karena dihitung otomatis.
        ], [
            'judul.required' => 'Judul tugas wajib diisi.',
            'deadline.required' => 'Deadline tugas wajib diisi.',
            'deadline.after' => 'Deadline harus setelah waktu sekarang.',
            'tipe.required' => 'Jenis tugas wajib dipilih.',
            'pengampu_kelas_id.required' => 'Mata pelajaran dan kelas wajib dipilih.',
            'soal.required_if' => 'Minimal harus ada satu soal pilihan ganda.',
            'soal.*.pertanyaan.required_if' => 'Pertanyaan wajib diisi.',
            'soal.*.pilihan.a.required_if' => 'Pilihan A wajib diisi.',
            'soal.*.pilihan.b.required_if' => 'Pilihan B wajib diisi.',
            'soal.*.pilihan.c.required_if' => 'Pilihan C wajib diisi.',
            'soal.*.pilihan.d.required_if' => 'Pilihan D wajib diisi.',
            'soal.*.pilihan.e.required_if' => 'Pilihan E wajib diisi.',
            'soal.*.kunci_jawaban.required_if' => 'Kunci jawaban wajib dipilih.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | 3. MENYIMPAN SOAL PILIHAN GANDA
    |--------------------------------------------------------------------------
    */
    private function simpanSoal(Tugas $tugas, Request $request)
    {
        // Hapus soal sebelumnya ketika tugas diperbarui.
        Soal::where('tugas_id', $tugas->id)->delete();

        // Tugas upload tidak membutuhkan soal pilihan ganda.
        if ($tugas->tipe !== 'pilihan_ganda') {
            return;
        }

        // Ambil seluruh soal dari form.
        $daftarSoal = $request->input('soal', []);
        $totalSoal = count($daftarSoal);

        // Hentikan jika tidak ada soal.
        if ($totalSoal === 0) {
            return;
        }

        // Total nilai maksimal seluruh soal adalah 100.
        $bobotPerSoal = 100 / $totalSoal;

        // Simpan setiap pertanyaan beserta pilihan dan kunci jawabannya.
        foreach ($daftarSoal as $index => $item) {
            Soal::create([
                'pertanyaan' => $item['pertanyaan'],
                'tipe_soal' => 'single_choice',

                'pilihan' => [
                    'a' => $item['pilihan']['a'],
                    'b' => $item['pilihan']['b'],
                    'c' => $item['pilihan']['c'],
                    'd' => $item['pilihan']['d'],
                    'e' => $item['pilihan']['e'],
                ],

                'kunci_jawaban' => strtolower($item['kunci_jawaban']),
                'bobot' => $bobotPerSoal,
                'urutan' => $index + 1,
                'quiz_id' => null,
                'tugas_id' => $tugas->id,
                'ujian_id' => null,
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | 4. MEMBUAT TUGAS BARU
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        // Validasi seluruh data dari form.
        $data = $this->validasiTugas($request);

        // Pastikan kelas dan mapel merupakan wewenang guru.
        $pengampu = PengampuKelas::with('guruMapel.mapel')
            ->where('id', $data['pengampu_kelas_id'])
            ->whereHas('guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })
            ->first();

        if (!$pengampu) {
            return back()->withInput()->withErrors([
                'pengampu_kelas_id' => 'Kelas atau mata pelajaran bukan wewenang Anda.'
            ]);
        }

        // Simpan file lampiran jika jenis tugas adalah upload.
        $filePath = null;

        if ($data['tipe'] === 'upload' && $request->hasFile('file_tugas_input')) {
            $filePath = $request->file('file_tugas_input')
                ->store('lampiran_tugas', 'public');
        }

        try {
            // Simpan tugas dan soal dalam satu transaksi database.
            $tugas = DB::transaction(function () use ($data, $request, $filePath) {
                $tugas = Tugas::create([
                    'judul' => $data['judul'],
                    'deskripsi' => $data['deskripsi'] ?? null,
                    'deadline' => Carbon::parse($data['deadline']),
                    'tipe' => $data['tipe'],
                    'file_lampiran' => $filePath,
                    'pengampu_kelas_id' => $data['pengampu_kelas_id'],
                ]);

                $this->simpanSoal($tugas, $request);

                return $tugas;
            });
        } catch (\Throwable $e) {
            // Hapus file jika penyimpanan database gagal.
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }

            throw $e;
        }

        // Kirim notifikasi tugas baru kepada siswa di kelas tujuan.
        $mapelNama = $pengampu->guruMapel?->mapel?->nama_mapel
            ?? 'Mata Pelajaran';

        $deadlineFormatted = Carbon::parse($data['deadline'])
            ->locale('id')
            ->isoFormat('D MMMM Y, HH:mm');

        Notifikasi::kirimKeKelas(
            $pengampu->kelas_id,
            "Tugas Baru: {$tugas->judul}",
            "Guru telah menerbitkan tugas baru untuk mata pelajaran {$mapelNama}. Batas pengumpulan: {$deadlineFormatted} WIB.",
            'tugas_baru',
            route('siswa.tugas.show', $tugas->id),
            $tugas->id
        );

        return redirect()->back()
            ->with('success', 'Tugas berhasil dipublikasikan!');
    }


    /*
    |--------------------------------------------------------------------------
    | 5. MENAMPILKAN DETAIL PENGUMPULAN TUGAS
    |--------------------------------------------------------------------------
    */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        // Pastikan pengguna merupakan guru.
        abort_unless($user?->role === 'guru' && $guru_id, 403);

        // Ambil tugas beserta relasi kelas, mapel, dan soal.
        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals'
        ])->findOrFail($id);

        // Pastikan tugas merupakan milik guru yang sedang login.
        if ($tugas->pengampuKelas?->guruMapel?->guru_id != $guru_id) {
            abort(403, 'Akses ditolak.');
        }

        // Ambil kelas dari tugas.
        $kelas = $tugas->pengampuKelas?->kelas;

        abort_unless($kelas, 404, 'Kelas tugas tidak ditemukan.');

        // Ambil seluruh siswa yang terdaftar pada kelas.
        // Periode disesuaikan dengan relasi Siswa yang kamu kirim.
        $siswas = $kelas->siswas()
            ->wherePivot('tahun_ajaran', '2026/2027')
            ->wherePivot('semester', 'ganjil')
            ->orderBy('siswas.nama_siswa')
            ->get();

        // Ambil seluruh data pengumpulan tugas.
        $pengumpulans = Nilai::with('siswa')
            ->where('tugas_id', $tugas->id)
            ->orderByDesc('id')
            ->get();

        // Jika ada lebih dari satu data pengumpulan per siswa,
        // gunakan data pengumpulan terbaru.
        $pengumpulanBySiswa = $pengumpulans
            ->unique('siswa_id')
            ->keyBy('siswa_id');

        // Ambil deadline tugas.
        $deadline = $tugas->deadline
            ? Carbon::parse($tugas->deadline)
            : null;

        // Gabungkan seluruh siswa dengan data pengumpulannya.
        // Siswa yang belum mengumpulkan tetap ditampilkan.
        $daftarSiswa = $siswas->map(function ($siswa) use (
            $pengumpulanBySiswa,
            $deadline
        ) {
            $pengumpulan = $pengumpulanBySiswa->get($siswa->id);

            // Waktu pengumpulan siswa.
            $waktuKumpul = $pengumpulan?->created_at
                ? Carbon::parse($pengumpulan->created_at)
                : null;

            // Periksa apakah siswa mengumpulkan setelah deadline.
            $terlambat = $waktuKumpul && $deadline
                ? $waktuKumpul->greaterThan($deadline)
                : false;

            // Tentukan status pengumpulan untuk filter.
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

        // Kirim data tugas dan daftar siswa ke halaman detail.
        return view('guru.kelola_tugas.show', compact(
            'tugas',
            'pengumpulans',
            'daftarSiswa'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | 6. MEMPERBARUI TUGAS
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        // Ambil tugas yang akan diperbarui.
        $tugas = Tugas::with('pengampuKelas.guruMapel')
            ->findOrFail($id);

        // Pastikan tugas merupakan milik guru.
        if ($tugas->pengampuKelas?->guruMapel?->guru_id != $guru_id) {
            abort(403, 'Akses ditolak.');
        }

        // Validasi data terbaru.
        $data = $this->validasiTugas($request);

        // Pastikan kelas/mapel tujuan juga diampu guru.
        $pengampu = PengampuKelas::where('id', $data['pengampu_kelas_id'])
            ->whereHas('guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })
            ->first();

        if (!$pengampu) {
            return back()->withInput()->withErrors([
                'pengampu_kelas_id' => 'Kelas atau mata pelajaran bukan wewenang Anda.'
            ]);
        }

        // Simpan informasi file lama.
        $fileLama = $tugas->file_lampiran;
        $fileBaru = null;

        // Jika guru mengunggah file baru, simpan file tersebut.
        if ($data['tipe'] === 'upload' && $request->hasFile('file_tugas_input')) {
            $fileBaru = $request->file('file_tugas_input')
                ->store('lampiran_tugas', 'public');
        }

        // Tentukan file yang akan digunakan setelah update.
        $filePath = $fileBaru
            ?? ($data['tipe'] === 'upload' ? $fileLama : null);

        try {
            DB::transaction(function () use ($data, $request, $tugas, $filePath) {
                $tugas->update([
                    'judul' => $data['judul'],
                    'deskripsi' => $data['deskripsi'] ?? null,
                    'deadline' => Carbon::parse($data['deadline']),
                    'tipe' => $data['tipe'],
                    'file_lampiran' => $filePath,
                    'pengampu_kelas_id' => $data['pengampu_kelas_id'],
                ]);

                // Perbarui soal sesuai data terbaru.
                $this->simpanSoal($tugas, $request);
            });
        } catch (\Throwable $e) {
            // Hapus file baru jika update gagal.
            if ($fileBaru) {
                Storage::disk('public')->delete($fileBaru);
            }

            throw $e;
        }

        // Hapus file lama jika sudah tidak digunakan.
        if (
            $fileLama &&
            $fileLama !== $filePath &&
            Storage::disk('public')->exists($fileLama)
        ) {
            Storage::disk('public')->delete($fileLama);
        }

        return redirect()->back()
            ->with('success', 'Tugas berhasil diperbarui!');
    }


    /*
    |--------------------------------------------------------------------------
    | 7. MENGHAPUS TUGAS
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        // Ambil tugas beserta relasi pemiliknya.
        $tugas = Tugas::with('pengampuKelas.guruMapel')
            ->findOrFail($id);

        // Pastikan tugas merupakan milik guru.
        if ($tugas->pengampuKelas?->guruMapel?->guru_id != $guru_id) {
            abort(403, 'Akses ditolak.');
        }

        $fileLama = $tugas->file_lampiran;

        // Hapus soal dan tugas dari database.
        DB::transaction(function () use ($tugas) {
            Soal::where('tugas_id', $tugas->id)->delete();

            $tugas->delete();
        });

        // Hapus file lampiran jika ada.
        if (
            $fileLama &&
            Storage::disk('public')->exists($fileLama)
        ) {
            Storage::disk('public')->delete($fileLama);
        }

        return redirect()->route('guru.tugas')
            ->with('success', 'Tugas berhasil dihapus!');
    }


    /*
    |--------------------------------------------------------------------------
    | 8. MEMBERIKAN NILAI KEPADA SISWA
    |--------------------------------------------------------------------------
    */
    public function berikanNilai(Request $request, string $id)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;

        abort_unless($user?->role === 'guru' && $guru_id, 403);

        // Validasi nilai yang dimasukkan guru.
        $data = $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
        ]);

        // Ambil data pengumpulan beserta tugas dan siswa.
        $pengumpulan = Nilai::with([
            'tugas.pengampuKelas.guruMapel',
            'siswa.user'
        ])->findOrFail($id);

        // Pastikan pengumpulan berasal dari tugas milik guru.
        if ($pengumpulan->tugas?->pengampuKelas?->guruMapel?->guru_id != $guru_id) {
            abort(403, 'Akses ditolak.');
        }

        // Simpan nilai dan ubah status pengumpulan.
        $pengumpulan->update([
            'nilai' => $data['nilai'],
            'status' => 'selesai',
        ]);

        // Kirim notifikasi nilai kepada siswa.
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
