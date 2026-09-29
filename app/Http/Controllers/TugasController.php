<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TugasController extends Controller
{
    // 1. Menampilkan Halaman Utama Daftar Tugas & Statistik Dynamic Cards
    public function index()
    {
        $tugases = Tugas::with(['guruMapel.mapel', 'guruMapel.kelas'])
            ->withCount([
                'nilais as pengumpulans_count',
                'nilais as pengumpulans_dinilai_count' => function ($query) {
                    $query->whereNotNull('nilai');
                }
            ])
            ->latest()
            ->get();

        $guruMapels = GuruMapel::with(['mapel', 'kelas'])->get();

        $kelases = $guruMapels->pluck('kelas')->unique('id')->filter();
        $mapels  = $guruMapels->pluck('mapel')->unique('id')->filter();
        // Hitung Total Tugas
        $totalTugas = $tugases->count();

        // Filter Tugas Aktif (Deadline masih di masa depan)
        $tugasAktif = $tugases->filter(function ($item) {
            return Carbon::parse($item->deadline)->isFuture();
        });

        $totalAktif = $tugasAktif->count();
        $totalSelesai = $totalTugas - $totalAktif;

        // Hitung Pengumpulan yang Perlu Dinilai
        $perluDinilai = Nilai::whereNull('nilai')->whereNotNull('tugas_id')->count();

        // Hitung Tugas Mendesak (Kurang dari 24 Jam / 1 Hari)
        $deadlineTerdekat = $tugasAktif->filter(function ($item) {
            $deadline = Carbon::parse($item->deadline);
            return $deadline->isFuture() && $deadline->lessThanOrEqualTo(now()->addHours(24));
        })->count();

        return view('Guru.Tugas', compact(
            'tugases',
            'guruMapels',
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
        $request->validate([
            'judul'            => 'required|string|max:200',
            'deskripsi'        => 'nullable|string',
            'deadline'         => 'required',
            'tipe'             => 'required|in:upload,pilihan_ganda',
            'guru_mapel_id'    => 'required|exists:guru_mapels,id',
            'file_tugas_input' => 'nullable|file|mimes:pdf,docx,jpg,png|max:10240',
        ]);

        $filePath = null;

        if ($request->hasFile('file_tugas_input')) {
            $filePath = $request->file('file_tugas_input')->store('lampiran_tugas', 'public');
        }

        Tugas::create([
            'judul'         => $request->judul,
            'deskripsi'     => $request->deskripsi,
            'deadline'      => Carbon::parse($request->deadline),
            'tipe'          => $request->tipe,
            'file_lampiran' => $filePath,
            'guru_mapel_id' => $request->guru_mapel_id,
        ]);

        return redirect()->back()->with('success', 'Tugas berhasil dipublikasikan!');
    }

    // 3. Menampilkan Detail Tugas & Pengumpulan Siswa (READ DETAIL)
    public function show(string $id)
    {
        $tugas = Tugas::with(['guruMapel.mapel', 'guruMapel.kelas'])->findOrFail($id);
        $pengumpulans = Nilai::with('siswa')->where('tugas_id', $id)->get();

        return view('Guru.DetailTugas', compact('tugas', 'pengumpulans'));
    }

    // 4. Memperbarui Data Tugas (UPDATE)
    public function update(Request $request, string $id)
    {
        $tugas = Tugas::findOrFail($id);

        $request->validate([
            'judul'            => 'required|string|max:200',
            'deskripsi'        => 'nullable|string',
            'deadline'         => 'required',
            'tipe'             => 'required|in:upload,pilihan_ganda',
            'guru_mapel_id'    => 'required|exists:guru_mapels,id',
            'file_tugas_input' => 'nullable|file|mimes:pdf,docx,jpg,png|max:10240',
        ]);

        $filePath = $tugas->file_lampiran;

        if ($request->hasFile('file_tugas_input')) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file_tugas_input')->store('lampiran_tugas', 'public');
        }

        $tugas->update([
            'judul'         => $request->judul,
            'deskripsi'     => $request->deskripsi,
            'deadline'      => Carbon::parse($request->deadline),
            'tipe'          => $request->tipe,
            'file_lampiran' => $filePath,
            'guru_mapel_id' => $request->guru_mapel_id,
        ]);

        return redirect()->back()->with('success', 'Tugas berhasil diperbarui!');
    }

    // 5. Menghapus Tugas (DELETE)
    public function destroy(string $id)
    {
        $tugas = Tugas::findOrFail($id);

        if ($tugas->file_lampiran && Storage::disk('public')->exists($tugas->file_lampiran)) {
            Storage::disk('public')->delete($tugas->file_lampiran);
        }

        $tugas->delete();

        return redirect()->route('guru.tugas')->with('success', 'Tugas berhasil dihapus!');
    }

    // 6. Menyimpan / Memperbarui Nilai Siswa (UPDATE NILAI)
    public function berikanNilai(Request $request, string $id)
    {
        $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
        ]);

        $pengumpulan = Nilai::findOrFail($id);
        $pengumpulan->update([
            'nilai'  => $request->nilai,
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
        $kelasId = $siswa?->kelas?->id;

        $daftarTugas = $kelasId
            ? Tugas::with([
                'pengampuKelas.guruMapel.mapel',
                'nilais' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id);
                },
            ])
                ->whereHas('pengampuKelas', function ($query) use ($kelasId) {
                    $query->where('kelas_id', $kelasId);
                })
                ->latest()
                ->get()
            : collect();

        $daftarTugas->each(function (Tugas $tugas): void {
            $tugas->setRelation('pengumpulanSiswa', $tugas->nilais->first());
        });

        return view('siswa.tugas.index', compact('daftarTugas'));
    }
}
