<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Siswa;
use App\Models\Ujian;
use Carbon\Carbon;

class UjianNotifikasiService
{
    /**
     * Kirim notifikasi saat ujian dipublikasikan atau dijadwalkan.
     * - Untuk ujian reguler: kirim ke kelas dengan filter target_agama mapel.
     * - Untuk ujian remidi/susulan: kirim HANYA ke peserta_khusus.
     */
    public function notifyUjianPublished(Ujian $ujian, bool $isScheduled = false): int
    {
        $ujian->loadMissing(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas']);
        $kelasId = $ujian->pengampuKelas?->kelas_id;
        if (!$kelasId) {
            return 0;
        }

        $mapel = $ujian->pengampuKelas?->guruMapel?->mapel;
        $mapelNama = $mapel?->nama_mapel ?? 'Mata Pelajaran';
        $targetAgama = $mapel?->target_agama;
        $jenis = $ujian->jenis_ujian ?? 'reguler';
        $isKhusus = in_array($jenis, ['remidi', 'susulan'], true);

        $jadwalInfo = '';
        if ($ujian->waktu_mulai) {
            $jadwalInfo = ' Jadwal mulai: ' . Carbon::parse($ujian->waktu_mulai)->locale('id')->translatedFormat('d M Y, H:i') . ' WIB.';
        }

        $url = route('siswa.ujian.show', $ujian->id);
        $data = [
            'resource_type' => 'ujian',
            'resource_id'   => $ujian->id,
            'mapel_id'      => $mapel?->id,
            'kelas_id'      => $kelasId,
            'jenis_ujian'   => $jenis,
        ];

        if ($isKhusus) {
            $pesertaKhusus = is_array($ujian->peserta_khusus) ? $ujian->peserta_khusus : [];
            if (empty($pesertaKhusus)) {
                return 0;
            }

            $labelJenis = $jenis === 'remidi' ? 'Ujian Remidi' : 'Ujian Susulan';
            $judul = "{$labelJenis}: {$ujian->judul}";
            $pesan = $isScheduled
                ? "Anda terdaftar sebagai peserta {$labelJenis} untuk mata pelajaran {$mapelNama}.{$jadwalInfo}"
                : "Anda terdaftar sebagai peserta {$labelJenis} untuk mata pelajaran {$mapelNama} dan ujian sudah dapat diakses.{$jadwalInfo}";

            return Notifikasi::kirimKeSiswaIds(
                $pesertaKhusus,
                $judul,
                $pesan,
                'ujian_remidi',
                $url,
                $ujian->id,
                $data
            );
        }

        $judul = $isScheduled
            ? "Jadwal Ujian Baru: {$ujian->judul}"
            : "Ujian Baru Dipublikasikan: {$ujian->judul}";
        $pesan = $isScheduled
            ? "Ujian evaluasi {$mapelNama} telah dijadwalkan.{$jadwalInfo}"
            : "Ujian evaluasi {$mapelNama} telah dipublikasikan dan dapat diakses sesuai jadwal.{$jadwalInfo}";

        return Notifikasi::kirimKeKelas(
            $kelasId,
            $judul,
            $pesan,
            'ujian_baru',
            $url,
            $ujian->id,
            $data,
            $targetAgama
        );
    }

    /**
     * Kirim / rollup notifikasi ke guru saat siswa menyelesaikan ujian.
     */
    public function notifyUjianSelesaiKeGuru(Ujian $ujian, Siswa $siswa, float $nilaiAkhir, bool $adaEssay = false): ?Notifikasi
    {
        $ujian->loadMissing(['pengampuKelas.guruMapel.guru.user', 'pengampuKelas.guruMapel.mapel', 'pengampuKelas.kelas']);
        $guruUser = $ujian->pengampuKelas?->guruMapel?->guru?->user;
        if (!$guruUser) {
            return null;
        }

        $namaSiswa = $siswa->nama_siswa ?? 'Siswa';
        $namaKelas = $ujian->pengampuKelas?->kelas?->nama_kelas ?? 'Kelas';
        $url = $adaEssay
            ? route('guru.ujian.koreksi-essay', $ujian->id)
            : route('guru.ujian.rekap', $ujian->id);

        $judulTunggal = $adaEssay
            ? "Ujian Selesai (Perlu Koreksi Essay): {$ujian->judul}"
            : "Ujian Selesai Dikerjakan: {$ujian->judul}";

        $pesanTunggal = $adaEssay
            ? "Siswa {$namaSiswa} ({$namaKelas}) telah menyelesaikan ujian '{$ujian->judul}'. Terdapat soal essay yang menunggu koreksi."
            : "Siswa {$namaSiswa} ({$namaKelas}) telah menyelesaikan ujian '{$ujian->judul}' dengan nilai {$nilaiAkhir}.";

        $labelAksi = $adaEssay
            ? 'Menyelesaikan Ujian (Perlu Koreksi Essay)'
            : 'Menyelesaikan Ujian';

        return Notifikasi::kirimAtauRollupKeGuru(
            $guruUser->id,
            $judulTunggal,
            $pesanTunggal,
            'ujian_selesai',
            $url,
            $ujian->id,
            $namaSiswa,
            $ujian->judul,
            $labelAksi,
            [
                'resource_type' => 'ujian',
                'resource_id'   => $ujian->id,
                'ujian_id'      => $ujian->id,
                'siswa_id'      => $siswa->id,
                'mapel_id'      => $ujian->pengampuKelas?->guruMapel?->mapel_id,
                'nilai'         => $nilaiAkhir,
                'perlu_koreksi' => $adaEssay,
            ]
        );
    }

    /**
     * Kirim atau perbarui notifikasi ke siswa saat nilai essay ujian dinilai guru.
     * Jika siswa masih memiliki notifikasi nilai_ujian belum dibaca untuk ujian ini,
     * perbarui agar tidak menerima spam saat guru menilai beberapa soal essay sekaligus.
     */
    public function notifyNilaiUjianSiswa(int $ujianId, int $siswaId, float $nilaiAkhir): ?Notifikasi
    {
        $ujian = Ujian::with('pengampuKelas.guruMapel.mapel')->find($ujianId);
        $siswa = Siswa::with('user')->find($siswaId);

        if (!$ujian || !$siswa || !$siswa->user) {
            return null;
        }

        $judul = "Nilai Ujian Diperbarui: {$ujian->judul}";
        $pesan = "Guru telah menilai jawaban essay pada ujian '{$ujian->judul}'. Nilai akhir Anda saat ini: {$nilaiAkhir}.";
        $url = route('siswa.ujian.hasil', $ujian->id);
        $data = [
            'resource_type' => 'ujian',
            'resource_id'   => $ujian->id,
            'ujian_id'      => $ujian->id,
            'mapel_id'      => $ujian->pengampuKelas?->guruMapel?->mapel_id,
            'nilai'         => $nilaiAkhir,
        ];

        $existing = Notifikasi::query()
            ->where('user_id', $siswa->user->id)
            ->where('tipe', 'nilai_ujian')
            ->where('id_referensi', $ujian->id)
            ->where('is_read', false)
            ->latest('updated_at')
            ->first();

        if ($existing) {
            $existing->update([
                'judul'      => $judul,
                'pesan'      => $pesan,
                'url'        => $url,
                'data'       => $data,
                'created_at' => now(),
            ]);

            return $existing->fresh();
        }

        return Notifikasi::kirim(
            $siswa->user->id,
            $judul,
            $pesan,
            'nilai_ujian',
            $url,
            $ujian->id,
            $data
        );
    }

    /**
     * Bersihkan seluruh notifikasi terkait ujian ketika ujian dihapus.
     */
    public function cleanupOnDelete(array $ujianIds): int
    {
        return Notifikasi::hapusByReferensi(
            ['ujian_baru', 'ujian_remidi', 'ujian_selesai', 'nilai_ujian', 'pengingat_ujian'],
            $ujianIds
        );
    }
}
