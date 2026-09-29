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
     * Halaman Daftar Tugas Siswa.
     */
    public function indexSiswa()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $siswaId = $user->siswa->id ?? null;

        // Ambil semua tugas beserta relasi nilai milik siswa yang sedang login
        $daftarTugas = Tugas::with(['guruMapel.mapel', 'guruMapel.kelas', 'nilais' => function ($query) use ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }])->latest()->get();

        return view('siswa.tugas.index', compact('daftarTugas'));
    }

    public function showSiswa(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $siswaId = $user->siswa->id ?? null;

        $tugas = Tugas::with(['guruMapel.mapel', 'guruMapel.kelas'])->findOrFail($id);

        $pengumpulan = Nilai::where('tugas_id', $id)
            ->where('siswa_id', $siswaId)
            ->first();

        return view('siswa.tugas.show', compact('tugas', 'pengumpulan'));
    }

    /*3. CREATE & UPDATE:*/
    public function kumpulkanTugas(Request $request, string $tugasId)
    {
        $request->validate([
            'file_jawaban' => 'required|file|mimes:pdf,doc,docx,zip,rar|max:10240',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $siswaId = $user->siswa->id ?? null;

        if (!$siswaId) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        // Hapus file lama jika siswa melakukan revisi
        $pengumpulanLama = Nilai::where('tugas_id', $tugasId)->where('siswa_id', $siswaId)->first();
        if ($pengumpulanLama && $pengumpulanLama->file_jawaban) {
            Storage::disk('public')->delete($pengumpulanLama->file_jawaban);
        }

        // Simpan file jawaban baru
        $filePath = $request->file('file_jawaban')->store('pengumpulan', 'public');

        $tugas = Tugas::findOrFail($tugasId);
        $statusPengumpulan = now()->gt($tugas->deadline) ? 'telat' : 'selesai';

        Nilai::updateOrCreate(
            [
                'tugas_id' => $tugasId,
                'siswa_id' => $siswaId,
            ],
            [
                'file_jawaban' => $filePath,
                'status'       => $statusPengumpulan, // 'telat' atau 'selesai' sesuai ENUM database
                'nilai'        => 0,                  // Beri 0 agar lolos NOT NULL di DB
            ]
        );

        return redirect()->back()->with('success', 'Tugas berhasil dikumpulkan!');
    }

    /**
     * 4. DELETE: Batalkan Pengumpulan & Hapus File Jawaban
     */
    public function batalkanPengumpulan(string $tugasId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $siswaId = $user->siswa->id ?? null;

        $pengumpulan = Nilai::where('tugas_id', $tugasId)->where('siswa_id', $siswaId)->first();

        if ($pengumpulan) {
            if ($pengumpulan->file_jawaban) {
                Storage::disk('public')->delete($pengumpulan->file_jawaban);
            }
            $pengumpulan->delete();
        }

        return redirect()->back()->with('success', 'Pengumpulan tugas berhasil dibatalkan.');
    }
}
