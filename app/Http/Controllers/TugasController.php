<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\GuruMapel;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    /**
     * Halaman Daftar Tugas Guru.
     */
    public function index()
    {
        return view('Guru.Tugas');
    }



    /**
     * Halaman Daftar Tugas Siswa
     */
    public function indexSiswa()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $siswa = $user->siswa;
        $siswaId = $siswa->id ?? null;

        // Perbaikan: Ganti 'guruMapel' menjadi 'pengampuKelas' sesuai struktur database
        $semuaTugas = Tugas::with([
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'nilais' => function ($query) use ($siswaId) {
                $query->where('siswa_id', $siswaId);
            }
        ])->latest()->get();

        // FILTER: Hanya ambil tugas yang BELUM dikumpulkan ATAU SUDAH dikumpul tapi BELUM DINILAI (nilai == 0 / null)
        $daftarTugas = $semuaTugas->filter(function ($tugas) {
            $pengumpulan = $tugas->nilais->first();

            // 1. Belum dikumpulkan sama sekali -> Tampilkan
            if (!$pengumpulan) {
                return true;
            }

            // 2. Sudah dikumpul TAPI belum dinilai (nilai 0 atau null) -> Tampilkan
            return $pengumpulan->nilai == 0 || $pengumpulan->nilai === null;
        });

        // Hitung data dinamis untuk Hero Banner
        $totalTugasCount = $semuaTugas->count();
        $tugasSelesaiCount = $semuaTugas->filter(function ($tugas) {
            return $tugas->nilais->isNotEmpty();
        })->count();

        $persenSelesai = $totalTugasCount > 0 ? round(($tugasSelesaiCount / $totalTugasCount) * 100) : 0;
        $tugasAktifCount = $semuaTugas->filter(fn($t) => $t->nilais->isEmpty())->count();
        $menungguPenilaianCount = $semuaTugas->filter(fn($t) => $t->nilais->first() && ($t->nilais->first()->nilai == 0 || $t->nilais->first()->nilai === null))->count();

        $tugasMendekatiTenggat = $semuaTugas->filter(function ($tugas) {
            return $tugas->nilais->isEmpty() && now()->lt($tugas->deadline);
        })->sortBy('deadline')->first();

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
