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
     * Menampilkan ruang ujian interaktif untuk siswa.
     */
    public function kerjakan(Ujian $ujian)
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $siswaId = $siswa ? $siswa->id : ($user->siswa_id ?? null);

        abort_unless($siswaId, 403, 'Profil siswa tidak ditemukan.');

        // Jika siswa sudah pernah mengumpulkan ujian ini, arahkan langsung ke halaman hasil
        $sesiSelesai = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->whereHas('nilais')
            ->first();

        if ($sesiSelesai) {
            return redirect()->route('siswa.ujian.hasil', $ujian->id);
        }

        // Buat atau ambil sesi di tabel seleksi_ujians
        $sesi = SeleksiUjian::firstOrCreate(
            [
                'ujian_id' => $ujian->id,
                'siswa_id' => $siswaId,
            ],
            [
                'nilai_awal'    => 0,
                'nilai_akhir'   => 0,
                'jumlah_remidi' => 0,
            ]
        );

        // Hitung sisa waktu pengerjaan
        $durasiMenit = $ujian->durasi_menit ?? 60;
        $waktuMulai = Carbon::parse($sesi->created_at);
        $batasSelesai = $waktuMulai->copy()->addMinutes($durasiMenit);

        if ($ujian->deadline) {
            $deadline = Carbon::parse($ujian->deadline);
            if ($deadline->lt($batasSelesai)) {
                $batasSelesai = $deadline;
            }
        }

        $sisaDetik = max(0, Carbon::now()->diffInSeconds($batasSelesai, false));

        $ujian->load([
            'pengampuKelas.guruMapel.mapel',
            'soals' => function ($query) {
                $query->orderBy('urutan')->orderBy('id');
            },
        ]);

        $savedAnswers = Menjawab::where('siswa_id', $siswaId)
            ->whereIn('soal_id', $ujian->soals->pluck('id'))
            ->pluck('jawaban_dipilih', 'soal_id');

        $soalsData = $ujian->soals->map(function ($soal, $index) {
            $pilihan = $soal->pilihan;
            if (is_string($pilihan)) {
                $pilihan = json_decode($pilihan, true) ?? [];
            }

            $formattedPilihan = [];
            if (is_array($pilihan)) {
                $huruf = ['A', 'B', 'C', 'D', 'E', 'F'];
                $i = 0;
                foreach ($pilihan as $key => $teks) {
                    $label = is_numeric($key) ? ($huruf[$i] ?? (string)$key) : strtoupper($key);
                    $formattedPilihan[] = [
                        'kode'  => strtolower($label),
                        'label' => $label,
                        'teks'  => $teks,
                    ];
                    $i++;
                }
            }

            return [
                'id'         => $soal->id,
                'nomor'      => $index + 1,
                'pertanyaan' => $soal->pertanyaan,
                'tipe_soal'  => $soal->tipe_soal,
                'bobot'      => $soal->bobot ?? 1,
                'gambar'     => $soal->gambar ? asset('storage/' . $soal->gambar) : null,
                'pilihan'    => $formattedPilihan,
            ];
        });

        return view('siswa.ujian.kerjakan', compact('ujian', 'soalsData', 'sisaDetik', 'savedAnswers'));
    }

    /**
     * Auto-save jawaban siswa ke tabel menjawabs.
     */
    public function simpanJawaban(Request $request, Ujian $ujian)
    {
        $request->validate([
            'soal_id' => 'required|exists:soals,id',
            'jawaban' => 'required|string',
        ]);

        $user = Auth::user();
        $siswa = $user->siswa;
        $siswaId = $siswa ? $siswa->id : ($user->siswa_id ?? null);

        $soal = Soal::where('ujian_id', $ujian->id)->findOrFail($request->soal_id);

        $jawabanUser = strtolower(trim((string)$request->jawaban));
        $kunci = $soal->kunci_jawaban;
        $isBenar = false;

        if (is_array($kunci)) {
            $cleanKunci = array_map(fn($k) => strtolower(trim((string)$k)), $kunci);
            $isBenar = in_array($jawabanUser, $cleanKunci);
        } else {
            $isBenar = ($jawabanUser === strtolower(trim((string)$kunci)));
        }

        Menjawab::updateOrCreate(
            [
                'soal_id'  => $soal->id,
                'siswa_id' => $siswaId,
            ],
            [
                'jawaban_dipilih' => $request->jawaban,
                'benar'           => $isBenar,
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Jawaban berhasil disimpan otomatis',
        ]);
    }

    /**
     * Submit ujian, kalkulasi skor akhir, dan simpan nilai.
     */
    public function submit(Request $request, Ujian $ujian)
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $siswaId = $siswa ? $siswa->id : ($user->siswa_id ?? null);

        abort_unless($siswaId, 403, 'Profil siswa tidak ditemukan.');

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->firstOrFail();

        return DB::transaction(function () use ($ujian, $siswaId, $sesi) {
            $soals = $ujian->soals;
            $soalIds = $soals->pluck('id');

            // Ambil semua jawaban siswa untuk soal ujian ini
            $jawabanSiswa = Menjawab::where('siswa_id', $siswaId)
                ->whereIn('soal_id', $soalIds)
                ->get()
                ->keyBy('soal_id');

            $totalBobot = $soals->sum('bobot') ?: 100;
            $skorDiperoleh = 0;

            foreach ($soals as $soal) {
                $jawab = $jawabanSiswa->get($soal->id);
                if ($jawab && $jawab->benar) {
                    $skorDiperoleh += ($soal->bobot ?? 1);
                }
            }

            // Normalisasi nilai akhir (skala 0 - 100)
            $nilaiAkhir = $totalBobot > 0 ? round(($skorDiperoleh / $totalBobot) * 100, 2) : 0;
            if ($nilaiAkhir > 100) $nilaiAkhir = 100;

            // Hitung durasi pengerjaan dalam detik
            $durasiDetik = (int) Carbon::parse($sesi->created_at)->diffInSeconds(now());

            // 1. Simpan nilai ke tabel seleksi_ujians
            $sesi->update([
                'nilai_awal'  => $nilaiAkhir,
                'nilai_akhir' => $nilaiAkhir,
            ]);

            // 2. Simpan nilai ke tabel nilais
            Nilai::updateOrCreate(
                [
                    'seleksi_ujian_id' => $sesi->id,
                    'siswa_id'         => $siswaId,
                ],
                [
                    'nilai'        => $nilaiAkhir,
                    'status'       => 'selesai',
                    'durasi'       => $durasiDetik,
                    'tugas_id'     => null,
                    'quiz_id'      => null,
                    'file_jawaban' => null,
                ]
            );

            return redirect()->route('siswa.ujian.hasil', $ujian->id)
                ->with('success', 'Ujian berhasil dikumpulkan!');
        });
    }

    /**
     * Menampilkan halaman rekapitulasi hasil ujian siswa.
     */
    public function hasil(Ujian $ujian)
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $siswaId = $siswa ? $siswa->id : ($user->siswa_id ?? null);

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->firstOrFail();

        $nilai = Nilai::where('seleksi_ujian_id', $sesi->id)
            ->where('siswa_id', $siswaId)
            ->first();

        abort_unless($nilai, 404, 'Hasil ujian belum tersedia.');

        $soals = $ujian->soals;
        $jawabanSiswa = Menjawab::where('siswa_id', $siswaId)
            ->whereIn('soal_id', $soals->pluck('id'))
            ->get();

        $totalSoal = $soals->count();
        $totalBenar = $jawabanSiswa->where('benar', true)->count();
        $totalSalah = max(0, $totalSoal - $totalBenar);

        $ujian->load(['pengampuKelas.guruMapel.mapel']);

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
