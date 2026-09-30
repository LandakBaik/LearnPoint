<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\PengampuKelas;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
                    $query->whereNotNull('nilai');
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

        // Filter Tugas Aktif (Deadline masih di masa depan)
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

        // Hitung Tugas Mendesak (Kurang dari 24 Jam / 1 Hari)
        $deadlineTerdekat = $tugasAktif->filter(function ($item) {
            $deadline = Carbon::parse($item->deadline);
            return $deadline->isFuture() && $deadline->lessThanOrEqualTo(now()->addHours(24));
        })->count();

        return view('Guru.Tugas', compact(
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

        return view('Guru.DetailTugas', compact('tugas', 'pengumpulans'));
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
