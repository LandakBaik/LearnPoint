<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasController extends Controller
{
    /**
     * Halaman Daftar Tugas Siswa.
     */
    public function index(Request $request)
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

    public function indexSiswa(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Halaman Detail Tugas Siswa.
     */
    public function show(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $siswa = $user->siswa;
        $siswaId = $siswa->id ?? null;

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

    public function showSiswa(string $id)
    {
        return $this->show($id);
    }

    /**
     * Kumpulkan Tugas Siswa.
     */
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

        // Cari tugas untuk cek deadline dan pemilik guru
        $tugas = Tugas::with(['pengampuKelas.guruMapel.guru.user', 'pengampuKelas.kelas'])->findOrFail($id);

        // Cek status pengumpulan (telat atau selesai)
        $isLate = now()->gt($tugas->deadline);
        $status = $isLate ? 'telat' : 'selesai';

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

        // Kirim notifikasi interaktif ke Guru pengampu
        $guruUser = $tugas->pengampuKelas?->guruMapel?->guru?->user;
        if ($guruUser) {
            $namaSiswa = $siswa->nama_siswa ?? $user->name;
            $namaKelas = $tugas->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';

            if ($isLate) {
                Notifikasi::kirim(
                    $guruUser->id,
                    "Pengumpulan Tugas Terlambat: {$tugas->judul}",
                    "Siswa {$namaSiswa} ({$namaKelas}) baru saja mengumpulkan tugas '{$tugas->judul}' melewati batas deadline.",
                    'tugas_terlambat',
                    route('guru.tugas.show', $tugas->id),
                    $tugas->id,
                    ['siswa_id' => $siswa->id, 'tugas_id' => $tugas->id, 'is_late' => true]
                );
            } else {
                Notifikasi::kirim(
                    $guruUser->id,
                    "Pengumpulan Tugas Masuk: {$tugas->judul}",
                    "Siswa {$namaSiswa} ({$namaKelas}) telah mengumpulkan tugas '{$tugas->judul}'.",
                    'tugas_dikumpulkan',
                    route('guru.tugas.show', $tugas->id),
                    $tugas->id,
                    ['siswa_id' => $siswa->id, 'tugas_id' => $tugas->id, 'is_late' => false]
                );
            }
        }

        return redirect()->back()->with('success', 'Tugas berhasil dikumpulkan!');
    }

    public function kumpulkan(Request $request, string $id)
    {
        return $this->kumpulkanTugas($request, $id);
    }
}

