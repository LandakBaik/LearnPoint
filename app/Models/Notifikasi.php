<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasis';

    public const TIPE_TUGAS = [
        'tugas_dikumpulkan',
        'tugas_terlambat',
        'tugas_baru',
        'nilai_tugas',
        'pengingat_deadline',
    ];

    public const TIPE_EVALUASI = [
        'kuis_selesai',
        'ujian_selesai',
        'kuis_baru',
        'ujian_baru',
        'ujian_remidi',
        'nilai_ujian',
        'pengingat_ujian',
        'pengingat_koreksi',
    ];

    public const TIPE_MATERI = [
        'materi_baru',
    ];

    public const TIPE_PENGINGAT = [
        'pengingat_deadline',
        'pengingat_ujian',
        'pengingat_koreksi',
    ];

    protected $fillable = [
        'judul',
        'pesan',
        'tipe',
        'url',
        'is_read',
        'read_at',
        'id_referensi',
        'user_id',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'data'    => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread(): bool
    {
        return $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Hitung seluruh badge kategori dalam 1 query agregat (hemat DB).
     *
     * @return array{total: int, unread: int, tugas: int, evaluasi: int, materi: int, pengingat: int}
     */
    public static function countsForUser(int $userId): array
    {
        $tugasPlaceholders = implode(',', array_fill(0, count(self::TIPE_TUGAS), '?'));
        $evalPlaceholders = implode(',', array_fill(0, count(self::TIPE_EVALUASI), '?'));
        $materiPlaceholders = implode(',', array_fill(0, count(self::TIPE_MATERI), '?'));
        $pengingatPlaceholders = implode(',', array_fill(0, count(self::TIPE_PENGINGAT), '?'));

        $bindings = array_merge(
            self::TIPE_TUGAS,
            self::TIPE_EVALUASI,
            self::TIPE_MATERI,
            self::TIPE_PENGINGAT
        );

        $row = self::query()
            ->where('user_id', $userId)
            ->selectRaw(
                "COUNT(*) as total, "
                . "SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread, "
                . "SUM(CASE WHEN tipe IN ({$tugasPlaceholders}) THEN 1 ELSE 0 END) as tugas, "
                . "SUM(CASE WHEN tipe IN ({$evalPlaceholders}) THEN 1 ELSE 0 END) as evaluasi, "
                . "SUM(CASE WHEN tipe IN ({$materiPlaceholders}) THEN 1 ELSE 0 END) as materi, "
                . "SUM(CASE WHEN tipe IN ({$pengingatPlaceholders}) THEN 1 ELSE 0 END) as pengingat",
                $bindings
            )
            ->first();

        return [
            'total'     => (int) ($row?->total ?? 0),
            'unread'    => (int) ($row?->unread ?? 0),
            'tugas'     => (int) ($row?->tugas ?? 0),
            'evaluasi'  => (int) ($row?->evaluasi ?? 0),
            'materi'    => (int) ($row?->materi ?? 0),
            'pengingat' => (int) ($row?->pengingat ?? 0),
        ];
    }

    /**
     * Helper kirim notifikasi ke user tertentu.
     */
    public static function kirim(int $userId, string $judul, string $pesan, string $tipe = 'info', ?string $url = null, ?int $idReferensi = null, ?array $data = null): ?self
    {
        return self::create([
            'user_id'      => $userId,
            'judul'        => $judul,
            'pesan'        => $pesan,
            'tipe'         => $tipe,
            'url'          => $url,
            'id_referensi' => $idReferensi,
            'data'         => $data,
            'is_read'      => false,
        ]);
    }

    /**
     * Helper kirim notifikasi ke akun user seorang guru.
     */
    public static function kirimKeGuru(int $guruId, string $judul, string $pesan, string $tipe = 'info', ?string $url = null, ?int $idReferensi = null, ?array $data = null): ?self
    {
        $guru = Guru::with('user')->find($guruId);
        if ($guru && $guru->user) {
            return self::kirim($guru->user->id, $judul, $pesan, $tipe, $url, $idReferensi, $data);
        }
        return null;
    }

    /**
     * Kirim atau kelompokkan (rollup) notifikasi untuk guru jika masih ada notifikasi
     * belum dibaca pada (user_id, tipe, id_referensi) yang sama dalam jendela waktu tertentu.
     * Mencegah inbox guru kebanjiran 30+ baris saat banyak siswa mengumpulkan bersamaan.
     */
    public static function kirimAtauRollupKeGuru(
        int $guruUserId,
        string $judulTunggal,
        string $pesanTunggal,
        string $tipe,
        ?string $url,
        ?int $idReferensi,
        string $namaSiswa,
        string $judulAktivitas,
        string $labelAksiRollup,
        array $extraData = []
    ): self {
        if ($idReferensi !== null) {
            $existing = self::query()
                ->where('user_id', $guruUserId)
                ->where('tipe', $tipe)
                ->where('id_referensi', $idReferensi)
                ->where('is_read', false)
                ->where('updated_at', '>=', now()->subHours(24))
                ->latest('updated_at')
                ->first();

            if ($existing) {
                $existingData = is_array($existing->data) ? $existing->data : [];
                $names = $existingData['rollup_names'] ?? [];

                if (empty($names) && !empty($existingData['nama_siswa'])) {
                    $names = [$existingData['nama_siswa']];
                }

                if (!in_array($namaSiswa, $names, true)) {
                    $names[] = $namaSiswa;
                }

                $count = max(count($names), ((int) ($existingData['rollup_count'] ?? 1)) + 1);
                if (count($names) === 1) {
                    $count = 1;
                }

                $ringkasanNama = match (true) {
                    count($names) <= 1 => $namaSiswa,
                    count($names) === 2 => "{$names[0]} dan {$names[1]}",
                    default => "{$names[0]}, {$names[1]}, dan " . ($count - 2) . " siswa lainnya",
                };

                $mergedData = array_merge($existingData, $extraData, [
                    'rollup_count' => $count,
                    'rollup_names' => array_slice($names, 0, 10),
                    'nama_siswa'   => $namaSiswa,
                ]);

                $existing->update([
                    'judul'      => "{$count} Siswa {$labelAksiRollup}: {$judulAktivitas}",
                    'pesan'      => "{$ringkasanNama} telah {$labelAksiRollup} '{$judulAktivitas}'.",
                    'url'        => $url ?? $existing->url,
                    'data'       => $mergedData,
                    'created_at' => now(),
                ]);

                return $existing->fresh();
            }
        }

        $data = array_merge($extraData, [
            'rollup_count' => 1,
            'rollup_names' => [$namaSiswa],
            'nama_siswa'   => $namaSiswa,
        ]);

        return self::kirim($guruUserId, $judulTunggal, $pesanTunggal, $tipe, $url, $idReferensi, $data);
    }

    /**
     * Helper kirim notifikasi massal ke daftar ID siswa tertentu (mis. peserta khusus remidi/susulan).
     */
    public static function kirimKeSiswaIds(
        array $siswaIds,
        string $judul,
        string $pesan,
        string $tipe = 'info',
        ?string $url = null,
        ?int $idReferensi = null,
        ?array $data = null
    ): int {
        $ids = collect($siswaIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return 0;
        }

        $userIds = User::where('status', '!=', 'nonaktif')
            ->whereHas('siswa', function ($sq) use ($ids) {
                $sq->where('status', '!=', 'nonaktif')
                    ->whereIn('id', $ids);
            })
            ->pluck('id');

        return self::bulkInsertForUserIds($userIds->all(), $judul, $pesan, $tipe, $url, $idReferensi, $data);
    }

    /**
     * Helper kirim notifikasi massal ke semua siswa dalam satu kelas (bulk insert).
     */
    public static function kirimKeKelas(int $kelasId, string $judul, string $pesan, string $tipe = 'info', ?string $url = null, ?int $idReferensi = null, ?array $data = null, ?string $targetAgama = null): int
    {
        $periodId = AcademicPeriod::currentId();

        $userIds = User::where('status', '!=', 'nonaktif')
            ->whereHas('siswa', function ($sq) use ($kelasId, $periodId, $targetAgama) {
                $sq->where('status', '!=', 'nonaktif')
                    ->when(filled($targetAgama), fn ($aq) => $aq->where('agama', $targetAgama))
                    ->whereHas('anggotaKelases', function ($ak) use ($kelasId, $periodId) {
                        $ak->where('kelas_id', $kelasId)
                            ->when($periodId, fn ($pq) => $pq->where('academic_period_id', $periodId));
                    });
            })
            ->pluck('id');

        return self::bulkInsertForUserIds($userIds->all(), $judul, $pesan, $tipe, $url, $idReferensi, $data);
    }

    /**
     * Bulk insert notifikasi ke sekumpulan user_id.
     */
    public static function bulkInsertForUserIds(
        array $userIds,
        string $judul,
        string $pesan,
        string $tipe = 'info',
        ?string $url = null,
        ?int $idReferensi = null,
        ?array $data = null
    ): int {
        $uniqueUserIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($uniqueUserIds)) {
            return 0;
        }

        $now = now();
        $encodedData = $data !== null ? json_encode($data) : null;

        $rows = array_map(fn (int $userId) => [
            'user_id'      => $userId,
            'judul'        => $judul,
            'pesan'        => $pesan,
            'tipe'         => $tipe,
            'url'          => $url,
            'id_referensi' => $idReferensi,
            'data'         => $encodedData,
            'is_read'      => false,
            'created_at'   => $now,
            'updated_at'   => $now,
        ], $uniqueUserIds);

        foreach (array_chunk($rows, 200) as $chunk) {
            self::insert($chunk);
        }

        return count($rows);
    }

    /**
     * Hapus notifikasi terkait saat resource (materi/tugas/kuis/ujian) dihapus
     * agar tidak meninggalkan link mati (404).
     */
    public static function hapusByReferensi(string|array $tipes, int|array $idReferensis): int
    {
        $tipeList = is_array($tipes) ? $tipes : [$tipes];
        $refList = is_array($idReferensis) ? $idReferensis : [$idReferensis];
        $refList = array_values(array_unique(array_filter(array_map('intval', $refList))));

        if (empty($tipeList) || empty($refList)) {
            return 0;
        }

        return self::query()
            ->whereIn('tipe', $tipeList)
            ->whereIn('id_referensi', $refList)
            ->delete();
    }
}
