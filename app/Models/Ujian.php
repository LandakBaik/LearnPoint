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
        'pengampu_kelas_id',
        'sesi',             // <-- Tambahan
        'durasi_menit',
        'kkm',
        'tampilkan_nilai',
        'waktu_mulai',
        'waktu_mulai_sesi', // <-- Tambahan
        'deadline',
        'status',
        'is_archived',
        'jenis_ujian',
        'parent_ujian_id',
        'kebijakan_remidi',
        'peserta_khusus',
        'token',
        'unlock_code',
        'dipublikasi_at',
    ];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'waktu_mulai_sesi' => 'datetime',
            'deadline' => 'datetime',
            'dipublikasi_at' => 'datetime',
            'peserta_khusus' => 'array',
            'tampilkan_nilai'  => 'boolean',
            'is_archived'      => 'boolean',
            'kkm' => 'decimal:2',
        ];
    }

    // =========================================================
    // RELASI
    // =========================================================
    public function parentUjian()
    {
        return $this->belongsTo(Ujian::class, 'parent_ujian_id');
    }
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
     * Ujian boleh masuk ke halaman edit jika berstatus:
     * - draft
     * - scheduled (selama belum ada siswa yang mengerjakan)
     * - unpublished & completed (hanya boleh mengubah bobot soal)
     */
    public function bolehDiedit(): bool
    {
        $this->sinkronisasiStatus();

        if (in_array($this->status, ['draft', 'unpublished', 'completed'], true)) {
            return true;
        }

        if ($this->status === 'scheduled') {
            $ujianIds = $this->id_grup_ujian
                ? self::where('id_grup_ujian', $this->id_grup_ujian)->pluck('id')
                : collect([$this->id]);

            return !\App\Models\SeleksiUjian::whereIn('ujian_id', $ujianIds)->exists();
        }

        return false;
    }

    /**
     * Menentukan apakah ujian hanya boleh diedit bobot/tingkat kesulitannya saja.
     */
    public function hanyaBisaEditBobot(): bool
    {
        return in_array($this->status, ['unpublished', 'completed'], true);
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
        // 1. JANGAN ubah jika status adalah draft atau sudah ditarik (unpublished)
        if (in_array($this->status, ['draft', 'unpublished'], true)) {
            return;
        }

        // Jika belum ada jadwal lengkap, lewati
        if (!$this->waktu_mulai || !$this->deadline) {
            return;
        }

        $now = now();
        $mulai = \Carbon\Carbon::parse($this->waktu_mulai);
        $deadline = \Carbon\Carbon::parse($this->deadline);

        // 2. Sinkronkan status hanya untuk ujian yang aktif berjalan atau terjadwal
        if ($now->greaterThan($deadline)) {
            if ($this->status !== 'completed') {
                $this->update(['status' => 'completed']);
            }
        } elseif ($now->greaterThanOrEqualTo($mulai)) {
            if ($this->status !== 'published') {
                $this->update(['status' => 'published']);
            }
        } else {
            if ($this->status !== 'scheduled') {
                $this->update(['status' => 'scheduled']);
            }
        }

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

