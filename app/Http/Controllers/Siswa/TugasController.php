<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    // 1. Daftar tugas siswa
    public function index()
    {
        $siswa = Auth::user()->siswa;

        if (!$siswa) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        $kelas = $siswa->kelas;
        $kelasId = $kelas?->id;

        $daftarTugas = $kelasId
            ? Tugas::with([
                'pengampuKelas.guruMapel.guru',
                'pengampuKelas.guruMapel.mapel',
                'pengampuKelas.kelas',
            ])
                ->whereHas('pengampuKelas', function ($query) use ($kelasId, $siswa) {
                    $query->currentPeriod()
                        ->where('kelas_id', $kelasId)
                        ->forSiswa($siswa);
                })
                ->latest()
                ->get()
            : collect();

        // Ambil pengumpulan masing-masing siswa dalam satu query (hindari N+1)
        $pengumpulanMap = $daftarTugas->isNotEmpty()
            ? Nilai::where('siswa_id', $siswa->id)
                ->whereIn('tugas_id', $daftarTugas->pluck('id'))
                ->get()
                ->keyBy('tugas_id')
            : collect();

        foreach ($daftarTugas as $tugas) {
            $tugas->pengumpulanSiswa = $pengumpulanMap->get($tugas->id);
        }

        // Tampilkan hanya tugas yang belum dikumpulkan
        $tugasBelumSelesai = $daftarTugas
            ->filter(fn($tugas) => $tugas->pengumpulanSiswa === null)
            ->values();

        // Filter urutan deadline
        $urutan = request('urutan', 'terbaru');

        if ($urutan === 'terlama') {
            $tugasBelumSelesai = $tugasBelumSelesai
                ->sortBy('deadline')
                ->values();
        } else {
            $tugasBelumSelesai = $tugasBelumSelesai
                ->sortByDesc('deadline')
                ->values();
        }
        // Statistik tugas
        $totalTugas = $daftarTugas->count();

        $tugasSelesai = $daftarTugas->filter(function ($tugas) {
            return $tugas->pengumpulanSiswa !== null;
        })->count();

        $persentase = $totalTugas > 0
            ? round(($tugasSelesai / $totalTugas) * 100)
            : 0;

        $belumDikumpulkan = $daftarTugas->filter(function ($tugas) {
            return $tugas->pengumpulanSiswa === null;
        })->count();

        $menungguPenilaian = $daftarTugas->filter(function ($tugas) {
            $pengumpulan = $tugas->pengumpulanSiswa;

            return $pengumpulan
                && $tugas->tipe === 'upload'
                && $pengumpulan->nilai === null;
        })->count();

        // Tugas dengan tenggat terdekat
        $tugasMendekatiTenggat = $daftarTugas
            ->filter(function ($tugas) {
                return $tugas->pengumpulanSiswa === null
                    && $tugas->deadline !== null;
            })
            ->sortBy('deadline')
            ->first();

        return view('siswa.tugas.index', compact(
            'siswa',
            'kelas',
            'daftarTugas',
            'tugasBelumSelesai',
            'totalTugas',
            'tugasSelesai',
            'persentase',
            'belumDikumpulkan',
            'menungguPenilaian',
            'tugasMendekatiTenggat'
        ));
    }


    // 2. Detail tugas siswa
    public function show(string $id)
    {
        $siswa = Auth::user()->siswa;

        if (!$siswa) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        $tugas = Tugas::with([
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
        ])->findOrFail($id);

        $kelas = $siswa->kelas;
        $kelasId = $kelas?->id;
        abort_unless($kelasId && $tugas->pengampuKelas?->kelas_id == $kelasId, 403, 'Anda tidak memiliki akses ke tugas ini.');

        $mapel = $tugas->pengampuKelas?->guruMapel?->mapel;
        if ($mapel && method_exists($mapel, 'isAccessibleBySiswa')) {
            abort_unless($mapel->isAccessibleBySiswa($siswa), 403, 'Mata pelajaran ini tidak sesuai dengan agama Anda.');
        }

        // Ambil pengumpulan siswa
        $pengumpulan = Nilai::where('siswa_id', $siswa->id)
            ->where('tugas_id', $tugas->id)
            ->first();

        // Urutkan soal
        $soals = $tugas->soals()
            ->orderBy('urutan')
            ->get();

        // Ambil jawaban siswa jika tugas pilihan ganda sudah dikerjakan
        $jawabanSiswa = collect();

        if ($tugas->tipe === 'pilihan_ganda' && $pengumpulan) {
            $jawabanSiswa = Menjawab::where('siswa_id', $siswa->id)
                ->whereIn('soal_id', $soals->pluck('id'))
                ->get()
                ->keyBy('soal_id');
        }

        // Tampilkan halaman sesuai jenis tugas
        if ($tugas->tipe === 'pilihan_ganda') {
            return view('siswa.tugas.show-pilgan', compact(
                'tugas',
                'siswa',
                'soals',
                'pengumpulan',
                'jawabanSiswa'
            ));
        }

        return view('siswa.tugas.show', compact(
            'tugas',
            'siswa',
            'pengumpulan'
        ));
    }


    // 3. Mengumpulkan tugas
    public function kumpulkanTugas(Request $request, string $id)
    {
        $siswa = Auth::user()->siswa;

        if (!$siswa) {
            abort(403, 'Data siswa tidak ditemukan.');
        }
        $tugas = Tugas::with([
            'soals',
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
        ])->findOrFail($id);

        $kelas = $siswa->kelas;
        $kelasId = $kelas?->id;
        abort_unless($kelasId && $tugas->pengampuKelas?->kelas_id == $kelasId, 403, 'Anda tidak memiliki akses ke tugas ini.');

        $mapel = $tugas->pengampuKelas?->guruMapel?->mapel;
        if ($mapel && method_exists($mapel, 'isAccessibleBySiswa')) {
            abort_unless($mapel->isAccessibleBySiswa($siswa), 403, 'Mata pelajaran ini tidak sesuai dengan agama Anda.');
        }

        $mapel = $tugas->pengampuKelas?->guruMapel?->mapel;
        if ($mapel && method_exists($mapel, 'isAccessibleBySiswa')) {
            abort_unless($mapel->isAccessibleBySiswa($siswa), 403, 'Mata pelajaran ini tidak sesuai dengan agama Anda.');
        }

        // Tentukan status pengumpulan
        $status = (
            $tugas->deadline &&
            now()->gt($tugas->deadline)
        ) ? 'telat' : 'selesai';


        // 4. Pengumpulan tugas pilihan ganda
        if ($tugas->tipe === 'pilihan_ganda') {

            // Cegah pengumpulan lebih dari satu kali
            $sudahMengumpulkan = Nilai::where('siswa_id', $siswa->id)
                ->where('tugas_id', $tugas->id)
                ->exists();

            if ($sudahMengumpulkan) {
                return back()->with(
                    'error',
                    'Kamu sudah mengumpulkan tugas ini dan tidak dapat mengubah jawaban.'
                );
            }

            // Validasi jawaban
            $validated = $request->validate([
                'jawaban' => ['required', 'array'],
                'jawaban.*' => ['required', 'in:a,b,c,d,e'],
            ]);

            $jawaban = $validated['jawaban'];

            // Ambil semua soal
            $soals = $tugas->soals()
                ->orderBy('urutan')
                ->get();

            if ($soals->isEmpty()) {
                return back()->with('error', 'Soal tugas belum tersedia.');
            }

            // Pastikan semua soal sudah dijawab dan tidak ada ID soal tambahan
            $idSoal = $soals->pluck('id')
                ->map(fn($id) => (string) $id)
                ->sort()
                ->values()
                ->all();

            $idJawaban = collect(array_keys($jawaban))
                ->map(fn($id) => (string) $id)
                ->sort()
                ->values()
                ->all();

            if ($idSoal !== $idJawaban) {
                return back()
                    ->withErrors(['jawaban' => 'Pastikan semua soal sudah dijawab.'])
                    ->withInput();
            }

            // Hitung nilai dan simpan jawaban
            $totalBobot = 0;
            $bobotBenar = 0;

            DB::transaction(function () use (
                $soals,
                $jawaban,
                $siswa,
                $tugas,
                $status,
                &$totalBobot,
                &$bobotBenar
            ) {
                foreach ($soals as $soal) {

                    $jawabanDipilih = strtolower(
                        trim($jawaban[$soal->id])
                    );

                    // Ambil kunci jawaban
                    $kunci = $soal->kunci_jawaban;

                    if (is_array($kunci)) {
                        $kunci = $kunci[0] ?? '';
                    }

                    $kunci = strtolower(trim((string) $kunci));

                    // Periksa jawaban
                    $benar = $jawabanDipilih === $kunci;

                    // Ambil bobot soal
                    $bobot = (float) ($soal->bobot ?? 1);

                    $totalBobot += $bobot;

                    if ($benar) {
                        $bobotBenar += $bobot;
                    }

                    // Simpan jawaban ke tabel menjawabs
                    Menjawab::create([
                        'soal_id' => $soal->id,
                        'siswa_id' => $siswa->id,
                        'jawaban_dipilih' => $jawabanDipilih,
                        'benar' => $benar,
                    ]);
                }

                // Hitung nilai akhir
                $nilaiAkhir = $totalBobot > 0
                    ? round(($bobotBenar / $totalBobot) * 100, 2)
                    : 0;

                // Simpan nilai akhir ke tabel nilais
                Nilai::create([
                    'siswa_id' => $siswa->id,
                    'tugas_id' => $tugas->id,
                    'nilai' => $nilaiAkhir,
                    'status' => $status,
                    'file_jawaban' => null,
                    'durasi' => null,
                ]);
            });

            // Kirim notifikasi kepada guru
            $guruUser = $tugas->pengampuKelas?->guruMapel?->guru?->user;

            if ($guruUser) {
                $isLate = $status === 'telat';
                $namaSiswa = $siswa->nama_siswa ?? Auth::user()->name;
                $namaKelas = $tugas->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';
                Notifikasi::kirim(
                    $guruUser->id,
                    ($isLate ? 'Pengumpulan Tugas Terlambat: ' : 'Tugas Pilihan Ganda Dikumpulkan: ') . $tugas->judul,
                    "Siswa {$namaSiswa} ({$namaKelas}) telah mengumpulkan tugas pilihan ganda '{$tugas->judul}'.",
                    $isLate ? 'tugas_terlambat' : 'tugas_dikumpulkan',
                    route('guru.tugas.show', $tugas->id),
                    $tugas->id,
                    [
                        'tugas_id' => $tugas->id,
                        'siswa_id' => $siswa->id,
                        'is_late' => $isLate,
                    ]
                );
            }
            // Ambil nilai yang baru disimpan
            $nilai = Nilai::where('siswa_id', $siswa->id)
                ->where('tugas_id', $tugas->id)
                ->first();

            return redirect()
                ->route('siswa.tugas.show', $tugas->id)
                ->with(
                    'success',
                    'Tugas berhasil dikumpulkan! Nilai kamu: ' . $nilai->nilai
                );
        }

        // 5. Pengumpulan tugas upload file

        $validated = $request->validate([
            'file_jawaban' => [
                'required',
                'file',
                'mimes:pdf,docx,jpg,jpeg,png',
                'max:10240',
            ],
        ]);

        // Ambil pengumpulan sebelumnya
        $pengumpulanLama = Nilai::where('siswa_id', $siswa->id)
            ->where('tugas_id', $tugas->id)
            ->first();

        if ($pengumpulanLama && $pengumpulanLama->nilai !== null) {
            return back()->with('error', 'Tugas sudah dinilai dan tidak dapat diubah lagi.');
        }

        // Simpan file baru ke storage/app/private/jawaban_tugas (disk local)
        $file = $request->file('file_jawaban')
            ->store('jawaban_tugas', 'local');

        // Simpan atau perbarui pengumpulan
        $pengumpulan = Nilai::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'tugas_id' => $tugas->id,
            ],
            [
                'file_jawaban' => $file,
                'nilai' => null,
                'status' => $status,
            ]
        );

        // Hapus file lama jika ada (baik di disk local maupun legacy public)
        if (
            $pengumpulanLama &&
            $pengumpulanLama->file_jawaban &&
            $pengumpulanLama->file_jawaban !== $file
        ) {
            Storage::disk('local')->delete($pengumpulanLama->file_jawaban);
            Storage::disk('public')->delete($pengumpulanLama->file_jawaban);
        }

        // Kirim notifikasi kepada guru
        $guruUser = $tugas->pengampuKelas?->guruMapel?->guru?->user;

        if ($guruUser) {
            $isLate = $status === 'telat';
            $namaSiswa = $siswa->nama_siswa ?? Auth::user()->name;
            $namaKelas = $tugas->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';
            Notifikasi::kirim(
                $guruUser->id,
                ($isLate ? 'Pengumpulan Tugas Terlambat: ' : 'Pengumpulan Tugas Masuk: ') . $tugas->judul,
                "Siswa {$namaSiswa} ({$namaKelas}) telah mengumpulkan tugas '{$tugas->judul}'" . ($isLate ? ' melewati batas deadline.' : '.'),
                $isLate ? 'tugas_terlambat' : 'tugas_dikumpulkan',
                route('guru.tugas.show', $tugas->id),
                $tugas->id,
                [
                    'tugas_id' => $tugas->id,
                    'siswa_id' => $siswa->id,
                    'is_late' => $isLate,
                ]
            );
        }

        return redirect()
            ->route('siswa.tugas.show', $tugas->id)
            ->with('success', 'Tugas berhasil dikumpulkan!');
    }

    // 6. Method tambahan
    public function indexSiswa()
    {
        return $this->index();
    }

    public function showSiswa(string $id)
    {
        return $this->show($id);
    }

    public function kumpulkan(Request $request, string $id)
    {
        return $this->kumpulkanTugas($request, $id);
    }
}
