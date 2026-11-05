<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $table = 'ujians';

    protected $fillable = [
        'id_grup_ujian',
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

    /**
     * Mengambil semua ujian dalam satu grup.
     */
    public function grupUjians()
    {
        return $this->hasMany(
            Ujian::class,
            'id_grup_ujian',
            'id_grup_ujian'
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

    /**
     * Memeriksa apakah ujian boleh diedit.
     */
    public function bolehDiedit(): bool
    {
        return in_array($this->status, [
            'draft',
            'unpublished',
            'completed',
        ], true);
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
// STATUS UJIAN
// =========================================================

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'scheduled' => 'Terjadwal',
            'published' => 'Dipublikasi',
            'unpublished' => 'Tidak Dipublikasi',
            'completed' => 'Selesai',
            default => 'Draft',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'scheduled' => 'warning',
            'published' => 'success',
            'unpublished' => 'secondary',
            'completed' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Menghitung status ujian berdasarkan waktu saat ini.
     */
    public function hitungStatusTerbaru(): string
    {
        if (!$this->waktu_mulai || !$this->deadline) {
            return 'draft';
        }

        if (now()->lt($this->waktu_mulai)) {
            return 'scheduled';
        }

        if (now()->lte($this->deadline)) {
            return 'published';
        }

        return 'completed';
    }

    /**
     * Memperbarui status di database jika berubah.
     */
    public function sinkronisasiStatus(): void
    {
        $statusBaru = $this->hitungStatusTerbaru();

        if ($this->status !== $statusBaru) {
            $this->status = $statusBaru;

            if ($statusBaru === 'published' && !$this->dipublikasi_at) {
                $this->dipublikasi_at = now();
            }

            $this->save();
        }
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

