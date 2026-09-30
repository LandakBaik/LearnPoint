<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $table = 'ujians';

    protected $fillable = [
        'judul',
        'deskripsi',
        'kisi_kisi',
        'waktu_mulai',
        'deadline',
        'durasi_menit',
        'kkm',
        'status',
        'dipublikasi_at',
        'pengampu_kelas_id',
        'guru_mapel_id',
    ];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'deadline' => 'datetime',
            'dipublikasi_at' => 'datetime',
            'kkm' => 'decimal:2',
        ];
    }

    // =========================================================
    // RELASI
    // =========================================================

    public function pengampuKelas()
    {
        return $this->belongsTo(
            PengampuKelas::class,
            'pengampu_kelas_id'
        );
    }

    public function soals()
    {
        return $this->hasMany(
            Soal::class,
            'ujian_id'
        )->orderBy('urutan');
    }

    public function seleksiUjians()
    {
        return $this->hasMany(
            SeleksiUjian::class,
            'ujian_id'
        );
    }

    // =========================================================
    // ACCESSOR
    // =========================================================

    public function getGuruMapelAttribute()
    {
        return $this->pengampuKelas?->guruMapel;
    }

    public function getGuruAttribute()
    {
        return $this->pengampuKelas?->guruMapel?->guru;
    }

    public function getMapelAttribute()
    {
        return $this->pengampuKelas?->guruMapel?->mapel;
    }

    public function getKelasAttribute()
    {
        return $this->pengampuKelas?->kelas;
    }

    // =========================================================
    // STATUS
    // =========================================================

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'scheduled' => 'Terjadwal',
            'published' => 'Dipublikasi',
            'finished' => 'Selesai',
            default => 'Draft',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'scheduled' => 'warning',
            'published' => 'success',
            'finished' => 'dark',
            default => 'secondary',
        };
    }

    // =========================================================
    // STATISTIK SOAL
    // =========================================================

    public function getTotalSoalAttribute(): int
    {
        return $this->soals()->count();
    }

    public function getTotalPoinAttribute(): float
    {
        return (float) $this->soals()->sum('bobot');
    }

    public function getQuestionTypeCountsAttribute(): array
    {
        return [
            'single_choice' => $this->soals()
                ->where('tipe_soal', 'single_choice')
                ->count(),

            'multiple_choice' => $this->soals()
                ->where('tipe_soal', 'multiple_choice')
                ->count(),

            'essay' => $this->soals()
                ->where('tipe_soal', 'essay')
                ->count(),

            'matching' => $this->soals()
                ->where('tipe_soal', 'matching')
                ->count(),
        ];
    }
}
