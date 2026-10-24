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
        'waktu_mulai',
        'waktu_selesai',
        'acak_jawaban',
        'level',
        'kesulitan',
        'pengampu_kelas_id',
    ];

    protected function casts(): array
    {
        return [
            'kesulitan_aktif' => 'array',
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
            'acak_jawaban' => 'boolean',
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

    /**
     * Cek apakah kuis sudah memasuki waktu mulai.
     */
    public function isStarted(): bool
    {
        return !$this->waktu_mulai || now()->greaterThanOrEqualTo($this->waktu_mulai);
    }

    /**
     * Cek apakah kuis telah melewati waktu selesai.
     */
    public function isEnded(): bool
    {
        return $this->waktu_selesai && now()->greaterThan($this->waktu_selesai);
    }

    /**
     * Cek apakah kuis sedang dalam rentang jadwal aktif.
     */
    public function isScheduleActive(): bool
    {
        return $this->isStarted() && !$this->isEnded();
    }

    /**
     * Informasi status jadwal kuis saat ini.
     */
    public function getScheduleInfoAttribute(): array
    {
        if (!$this->waktu_mulai && !$this->waktu_selesai) {
            return [
                'status'      => 'fleksibel',
                'label'       => 'Jadwal Fleksibel',
                'badge'       => 'bg-slate-100 text-slate-700 border-slate-200',
                'badge_class' => 'bg-slate-100 text-slate-700 border-slate-200',
                'dot'         => 'bg-slate-400',
                'description' => 'Kuis dapat dikerjakan kapan saja',
            ];
        }

        if ($this->waktu_mulai && now()->lessThan($this->waktu_mulai)) {
            return [
                'status'      => 'upcoming',
                'label'       => 'Belum Dimulai',
                'badge'       => 'bg-amber-50 text-amber-700 border-amber-200',
                'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'dot'         => 'bg-amber-500',
                'description' => 'Kuis belum dapat dikerjakan siswa hingga waktu mulai tercapai',
            ];
        }

        if ($this->waktu_selesai && now()->greaterThan($this->waktu_selesai)) {
            return [
                'status'      => 'expired',
                'label'       => 'Sudah Berakhir',
                'badge'       => 'bg-rose-50 text-rose-700 border-rose-200',
                'badge_class' => 'bg-rose-50 text-rose-700 border-rose-200',
                'dot'         => 'bg-rose-500',
                'description' => 'Batas waktu pengerjaan kuis telah berakhir',
            ];
        }

        return [
            'status'      => 'active',
            'label'       => 'Sedang Berlangsung',
            'badge'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot'         => 'bg-emerald-500',
            'description' => 'Kuis sedang aktif dan dapat dikerjakan',
        ];
    }
}
