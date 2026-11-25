<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Nilai;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Unduh atau tampilkan berkas materi dengan pemeriksaan hak akses.
     */
    public function materi(Request $request, Materi $materi)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $materi->loadMissing(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas']);

        if ($user->role === 'guru') {
            $guruId = $user->guru?->id;
            abort_unless(
                $guruId && (int) $materi->pengampuKelas?->guruMapel?->guru_id === (int) $guruId,
                403,
                'Anda tidak memiliki akses ke berkas materi ini.'
            );
        } elseif ($user->role === 'siswa') {
            $siswa = $user->siswa;
            abort_unless($siswa, 403, 'Profil siswa tidak ditemukan.');

            $anggotaKelas = $siswa->anggotaKelases()?->latest()?->first();
            $kelasId = $siswa->kelas?->id ?? $anggotaKelas?->kelas?->id;

            abort_unless(
                $kelasId && (int) $materi->pengampuKelas?->kelas_id === (int) $kelasId,
                403,
                'Anda tidak memiliki akses ke berkas materi kelas lain.'
            );

            $mapel = $materi->pengampuKelas?->guruMapel?->mapel;
            if ($mapel && method_exists($mapel, 'isAccessibleBySiswa')) {
                abort_unless(
                    $mapel->isAccessibleBySiswa($siswa),
                    403,
                    'Mata pelajaran ini tidak sesuai dengan agama Anda.'
                );
            }
        } elseif (!in_array($user->role, ['operator', 'admin', 'kepala_sekolah'], true)) {
            abort(403);
        }

        return $this->servePrivateOrLegacyFile($materi->file_materi, $request->boolean('download'));
    }

    /**
     * Unduh atau tampilkan berkas lampiran tugas dengan pemeriksaan hak akses.
     */
    public function tugas(Request $request, Tugas $tugas)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $tugas->loadMissing(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas']);

        if ($user->role === 'guru') {
            $guruId = $user->guru?->id;
            abort_unless(
                $guruId && (int) $tugas->pengampuKelas?->guruMapel?->guru_id === (int) $guruId,
                403,
                'Anda tidak memiliki akses ke lampiran tugas ini.'
            );
        } elseif ($user->role === 'siswa') {
            $siswa = $user->siswa;
            abort_unless($siswa, 403, 'Profil siswa tidak ditemukan.');

            $kelasId = $siswa->kelas()->value('kelases.id')
                ?? $siswa->anggotaKelases()?->latest()?->first()?->kelas_id;

            abort_unless(
                $kelasId && (int) $tugas->pengampuKelas?->kelas_id === (int) $kelasId,
                403,
                'Anda tidak memiliki akses ke lampiran tugas kelas lain.'
            );

            $mapel = $tugas->pengampuKelas?->guruMapel?->mapel;
            if ($mapel && method_exists($mapel, 'isAccessibleBySiswa')) {
                abort_unless(
                    $mapel->isAccessibleBySiswa($siswa),
                    403,
                    'Mata pelajaran ini tidak sesuai dengan agama Anda.'
                );
            }
        } elseif (!in_array($user->role, ['operator', 'admin', 'kepala_sekolah'], true)) {
            abort(403);
        }

        return $this->servePrivateOrLegacyFile($tugas->file_lampiran, $request->boolean('download'));
    }

    /**
     * Unduh atau tampilkan berkas jawaban tugas siswa dengan pemeriksaan hak akses.
     */
    public function jawaban(Request $request, Nilai $nilai)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $nilai->loadMissing(['tugas.pengampuKelas.guruMapel']);

        if ($user->role === 'siswa') {
            $siswaId = $user->siswa?->id;
            abort_unless(
                $siswaId && (int) $nilai->siswa_id === (int) $siswaId,
                403,
                'Anda tidak memiliki akses ke berkas jawaban siswa lain.'
            );
        } elseif ($user->role === 'guru') {
            $guruId = $user->guru?->id;
            abort_unless(
                $guruId && (int) $nilai->tugas?->pengampuKelas?->guruMapel?->guru_id === (int) $guruId,
                403,
                'Anda tidak memiliki akses ke berkas jawaban tugas ini.'
            );
        } elseif (!in_array($user->role, ['operator', 'admin', 'kepala_sekolah'], true)) {
            abort(403);
        }

        return $this->servePrivateOrLegacyFile($nilai->file_jawaban, $request->boolean('download'));
    }

    /**
     * Melayani berkas dari disk 'local' (privat), dengan fallback ke disk 'public' untuk berkas lama.
     */
    private function servePrivateOrLegacyFile(?string $path, bool $forceDownload = false)
    {
        if (empty($path)) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        $disk = null;
        if (Storage::disk('local')->exists($path)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($path)) {
            $disk = 'public';
        }

        if (!$disk) {
            abort(404, 'Berkas tidak ditemukan di penyimpanan.');
        }

        $filename = basename($path);

        if ($forceDownload) {
            return Storage::disk($disk)->download($path, $filename);
        }

        return Storage::disk($disk)->response($path, $filename);
    }
}
