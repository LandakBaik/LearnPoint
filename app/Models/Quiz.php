<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $table = 'quizzes';

    protected $fillable = [
        'judul',
        'deskripsi',
        'status',
        'kesulitan_aktif',
        'level',
        'kesulitan',
        'pengampu_kelas_id',
    ];

    protected function casts(): array
    {
        return [
            'kesulitan_aktif' => 'array',
        ];
    }

    public const KESULITAN_LIST = ['Mudah', 'Sedang', 'Sulit'];
    public const LEVEL_LIST = ['Level 1', 'Level 2', 'Level 3', 'Level 4', 'Level 5'];
    public const MAX_LEVEL = 5;
    public const MAX_SOAL = 10;

    public function pengampuKelas()
    {
        return $this->belongsTo(PengampuKelas::class, 'pengampu_kelas_id');
    }

    public function getGuruMapelAttribute()
    {
        return $this->pengampuKelas?->guruMapel;
    }

    public function soals()
    {
        return $this->hasMany(Soal::class, 'quiz_id');
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class, 'quiz_id');
    }

    /**
     * Mengambil daftar tingkat kesulitan (Arc) yang aktif pada kuis ini.
     * Default: ['Mudah', 'Sedang', 'Sulit'].
     */
    public function getKesulitanAktifArrayAttribute(): array
    {
        $aktif = $this->kesulitan_aktif;
        if (is_array($aktif) && count($aktif) > 0) {
            return array_values(array_intersect(self::KESULITAN_LIST, $aktif));
        }
        return self::KESULITAN_LIST;
    }

    /**
     * Query soal untuk arc (kesulitan) dan level tertentu.
     */
    public function soalsFor(string $kesulitan, string $level)
    {
        return $this->soals()
            ->where(function ($q) use ($kesulitan, $level) {
                $q->where('kesulitan', $kesulitan)
                  ->where('level', $level);
            });
    }

    /**
     * Hitung butir soal pada arc dan level tertentu.
     */
    public function countSoalsFor(string $kesulitan, string $level): int
    {
        return $this->soalsFor($kesulitan, $level)->count();
    }

    /**
     * Apakah masih bisa menambah soal di arc & level ini (Maksimal 10 soal).
     */
    public function canAddSoalFor(string $kesulitan, string $level): bool
    {
        return $this->countSoalsFor($kesulitan, $level) < self::MAX_SOAL;
    }

    /**
     * Hitung sisa slot butir soal yang masih bisa dibuat di level ini.
     */
    public function sisaSlotSoalFor(string $kesulitan, string $level): int
    {
        return max(0, self::MAX_SOAL - $this->countSoalsFor($kesulitan, $level));
    }

    /**
     * Helper status draf / published.
     */
    public function isDraft(): bool
    {
        return ($this->status ?? 'draft') === 'draft';
    }

    public function isPublished(): bool
    {
        return ($this->status ?? 'draft') === 'published';
    }
}
