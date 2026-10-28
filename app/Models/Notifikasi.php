<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasis';

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
     * Helper kirim notifikasi massal ke semua siswa dalam satu kelas.
     */
    public static function kirimKeKelas(int $kelasId, string $judul, string $pesan, string $tipe = 'info', ?string $url = null, ?int $idReferensi = null, ?array $data = null): int
    {
        $siswas = Siswa::with('user')
            ->whereHas('anggotaKelases', fn($q) => $q->where('kelas_id', $kelasId))
            ->get();

        $count = 0;
        foreach ($siswas as $siswa) {
            if ($siswa->user) {
                self::kirim($siswa->user->id, $judul, $pesan, $tipe, $url, $idReferensi, $data);
                $count++;
            }
        }

        return $count;
    }
}
