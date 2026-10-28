<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\PengampuKelas;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    // 1. Menampilkan Halaman Utama Daftar Tugas & Statistik Dynamic Cards
    public function index(Request $request)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $tugases = Tugas::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])
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

        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })
            ->get();

        $kelases = $pengampuKelases->pluck('kelas')->unique('id')->filter();
        $mapels = $pengampuKelases->pluck('guruMapel.mapel')->unique('id')->filter();

        // Hitung Total Tugas
        $totalTugas = $tugases->count();

        // Filter Tugas Aktif
        $tugasAktif = $tugases->filter(function ($item) {
            return Carbon::parse($item->deadline)->isFuture();
        });

        $totalAktif = $tugasAktif->count();
        $totalSelesai = $totalTugas - $totalAktif;

        // Hitung Pengumpulan yang Perlu Dinilai
        $perluDinilai = Nilai::whereNull('nilai')
            ->whereHas('tugas.pengampuKelas.guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })
            ->count();

        // Hitung Tugas Mendesak
        $deadlineTerdekat = $tugasAktif->filter(function ($item) {
            $deadline = Carbon::parse($item->deadline);
            return $deadline->isFuture() && $deadline->lessThanOrEqualTo(now()->addHours(24));
        })->count();

        return view('guru.tugas.index', compact(
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

    // 2. Menyimpan Tugas Baru (CREATE)
    public function store(Request $request)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'deadline' => 'required',
            'tipe' => 'required|in:upload,pilihan_ganda',
            'pengampu_kelas_id' => 'required|exists:pengampu_kelas,id',
            'file_tugas_input' => 'nullable|file|mimes:pdf,docx,jpg,png|max:10240',
        ]);

        // Verifikasi kepemilikan pengampu_kelas
        $pengampu = PengampuKelas::where('id', $request->pengampu_kelas_id)
            ->whereHas('guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })->first();

        if (! $pengampu) {
            return back()->withInput()->withErrors(['pengampu_kelas_id' => 'Kelas/Mapel tidak valid atau bukan wewenang Anda.']);
        }

        $filePath = null;
        if ($request->hasFile('file_tugas_input')) {
            $filePath = $request->file('file_tugas_input')->store('lampiran_tugas', 'public');
        }

        Tugas::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'deadline' => Carbon::parse($request->deadline),
            'tipe' => $request->tipe,
            'file_lampiran' => $filePath,
            'pengampu_kelas_id' => $request->pengampu_kelas_id,
        ]);

        return redirect()->back()->with('success', 'Tugas berhasil dipublikasikan!');
    }

    // 3. Menampilkan Detail Tugas & Pengumpulan Siswa (READ DETAIL)
    public function show(string $id)
    {
        $user = request()->user();
        $guru_id = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $tugas = Tugas::with(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas'])->findOrFail($id);

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guru_id) {
            abort(403, 'Akses ditolak');
        }

        $pengumpulans = Nilai::with('siswa')->where('tugas_id', $id)->get();

        return view('guru.tugas.show', compact('tugas', 'pengumpulans'));
    }

    // 4. Memperbarui Data Tugas (UPDATE)
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $tugas = Tugas::with('pengampuKelas.guruMapel')->findOrFail($id);

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guru_id) {
            abort(403, 'Akses ditolak');
        }

        $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'deadline' => 'required',
            'tipe' => 'required|in:upload,pilihan_ganda',
            'pengampu_kelas_id' => 'required|exists:pengampu_kelas,id',
            'file_tugas_input' => 'nullable|file|mimes:pdf,docx,jpg,png|max:10240',
        ]);

        // Verifikasi kepemilikan pengampu_kelas
        $pengampu = PengampuKelas::where('id', $request->pengampu_kelas_id)
            ->whereHas('guruMapel', function ($query) use ($guru_id) {
                $query->where('guru_id', $guru_id);
            })->first();

        if (! $pengampu) {
            return back()->withInput()->withErrors(['pengampu_kelas_id' => 'Kelas/Mapel tidak valid atau bukan wewenang Anda.']);
        }

        $filePath = $tugas->file_lampiran;

        if ($request->hasFile('file_tugas_input')) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file_tugas_input')->store('lampiran_tugas', 'public');
        }

        $tugas->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'deadline' => Carbon::parse($request->deadline),
            'tipe' => $request->tipe,
            'file_lampiran' => $filePath,
            'pengampu_kelas_id' => $request->pengampu_kelas_id,
        ]);

        return redirect()->back()->with('success', 'Tugas berhasil diperbarui!');
    }

    // 5. Menghapus Tugas (DELETE)
    public function destroy(string $id)
    {
        $user = request()->user();
        $guru_id = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $tugas = Tugas::with('pengampuKelas.guruMapel')->findOrFail($id);

        if ($tugas->pengampuKelas?->guruMapel?->guru_id !== $guru_id) {
            abort(403, 'Akses ditolak');
        }

        if ($tugas->file_lampiran && Storage::disk('public')->exists($tugas->file_lampiran)) {
            Storage::disk('public')->delete($tugas->file_lampiran);
        }

        $tugas->delete();

        return redirect()->route('guru.tugas')->with('success', 'Tugas berhasil dihapus!');
    }

    // 6. Menyimpan / Memperbarui Nilai Siswa (UPDATE NILAI)
    public function berikanNilai(Request $request, string $id)
    {
        $user = $request->user();
        $guru_id = $user?->guru?->id;
        abort_unless($user?->role === 'guru' && $guru_id, 403);

        $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
        ]);

        $pengumpulan = Nilai::with('tugas.pengampuKelas.guruMapel')->findOrFail($id);

        if ($pengumpulan->tugas?->pengampuKelas?->guruMapel?->guru_id !== $guru_id) {
            abort(403, 'Akses ditolak');
        }

        $pengumpulan->update([
            'nilai' => $request->nilai,
            'status' => 'selesai',
        ]);

        return redirect()->back()->with('success', 'Nilai siswa berhasil disimpan!');
    }

    /**
     * Halaman Daftar Tugas Siswa.
     */
    public function indexSiswa(Request $request)
    {
        $siswa = $request->user()?->siswa;
        $siswaId = $siswa?->id;

        // Ambil ID Kelas dengan aman
        $anggotaKelas = $siswa?->anggotaKelases()?->latest()?->first();
        $kelasId = $siswa?->kelas_id ?? $siswa?->kelas?->id ?? $anggotaKelas?->kelas_id;

        $semuaTugas = Tugas::with([
                'pengampuKelas.guruMapel.guru',
                'pengampuKelas.guruMapel.mapel',
                'pengampuKelas.kelas',
                'nilais' => function ($query) use ($siswaId) {
                    $query->where('siswa_id', $siswaId);
                }
            ])
            ->when($kelasId, function ($query) use ($kelasId) {
                $query->whereHas('pengampuKelas', function ($q) use ($kelasId) {
                    $q->where('kelas_id', $kelasId);
                });
            })
            ->latest()
            ->get();

        if ($semuaTugas->isEmpty()) {
            $semuaTugas = Tugas::with([
                    'pengampuKelas.guruMapel.guru',
                    'pengampuKelas.guruMapel.mapel',
                    'pengampuKelas.kelas',
                    'nilais' => function ($query) use ($siswaId) {
                        $query->where('siswa_id', $siswaId);
                    }
                ])
                ->latest()
                ->get();
        }

        $semuaTugas->each(function (Tugas $tugas): void {
            $tugas->setRelation('pengumpulanSiswa', $tugas->nilais->first());
        });

        $daftarTugas = $semuaTugas->filter(function ($tugas) {
            $pengumpulan = $tugas->nilais->first();

            // Belum dikumpul -> Tampilkan
            if (!$pengumpulan) {
                return true;
            }

            // Sudah dikumpul tapi belum ada nilai (nilai == 0 atau null) -> Tampilkan
            return $pengumpulan->nilai == 0 || $pengumpulan->nilai === null;
        });

        if ($daftarTugas->isEmpty() && $semuaTugas->isNotEmpty()) {
            $daftarTugas = $semuaTugas;
        }

        $totalTugasCount = $semuaTugas->count();
        $tugasSelesaiCount = $semuaTugas->filter(fn($t) => $t->nilais->isNotEmpty())->count();
        $persenSelesai = $totalTugasCount > 0 ? round(($tugasSelesaiCount / $totalTugasCount) * 100) : 0;
        
        $tugasAktifCount = $semuaTugas->filter(fn($t) => $t->nilais->isEmpty())->count();
        $menungguPenilaianCount = $semuaTugas->filter(function ($t) {
            $p = $t->nilais->first();
            return $p && ($p->nilai == 0 || $p->nilai === null);
        })->count();

        $tugasMendekatiTenggat = $semuaTugas->filter(fn($t) => $t->nilais->isEmpty())->sortBy('deadline')->first()
            ?? $semuaTugas->first();

        return view('siswa.tugas.index', compact(
            'daftarTugas',
            'siswa',
            'totalTugasCount',
            'tugasSelesaiCount',
            'persenSelesai',
            'tugasAktifCount',
            'menungguPenilaianCount',
            'tugasMendekatiTenggat'
        ));
    }
    public function showSiswa(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $siswa = $user->siswa;
        $siswaId = $siswa->id ?? null;

        // Perbaikan: Ganti 'guruMapel' menjadi 'pengampuKelas'
        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas'
        ])->findOrFail($id);

        $pengumpulan = Nilai::where('tugas_id', $id)
            ->where('siswa_id', $siswaId)
            ->first();

        return view('siswa.tugas.show', compact('tugas', 'pengumpulan', 'siswa'));
    }

    public function kumpulkanTugas(Request $request, string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $request->validate([
            'file_jawaban' => 'required|file|mimes:pdf,doc,docx,zip,rar|max:5000',
        ]);

        // Cari tugas untuk cek deadline
        $tugas = Tugas::findOrFail($id);

        // Cek status pengumpulan (telat atau selesai)
        $status = now()->gt($tugas->deadline) ? 'telat' : 'selesai';

        $filePath = null;
        if ($request->hasFile('file_jawaban')) {
            $filePath = $request->file('file_jawaban')->store('pengumpulan_tugas', 'public');
        }

        // Simpan ke tabel nilais
        Nilai::updateOrCreate(
            [
                'tugas_id' => $id,
                'siswa_id' => $siswa->id,
            ],
            [
                'file_jawaban' => $filePath,
                'status'       => $status, // 'telat' atau 'selesai' sesuai enum di DB
                'nilai'        => 0,        // Default 0 sebelum dinilai guru
            ]
        );

        return redirect()->back()->with('success', 'Tugas berhasil dikumpulkan!');
    }
}
