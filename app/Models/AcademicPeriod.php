<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class AcademicPeriod extends Model
{
    protected $table = 'academic_periods';

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'       => 'boolean',
            'tanggal_mulai'   => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function anggotaKelases(): HasMany
    {
        return $this->hasMany(AnggotaKelas::class, 'academic_period_id');
    }

    public function pengampuKelases(): HasMany
    {
        return $this->hasMany(PengampuKelas::class, 'academic_period_id');
    }

    public function getLabelAttribute(): string
    {
        return "{$this->tahun_ajaran} • " . ucfirst($this->semester);
    }

    /**
     * Nilai urutan kronologis untuk membedakan periode lampau vs mendatang.
     */
    public function getSortRankAttribute(): int
    {
        $startYear = 2026;
        if (preg_match('/^(\d{4})/', (string) $this->tahun_ajaran, $m)) {
            $startYear = (int) $m[1];
        }

        return ($startYear * 10) + ($this->semester === 'genap' ? 2 : 1);
    }

    /**
     * Mengambil periode akademik yang sedang aktif.
     */
    public static function current(): self
    {
        if (!Schema::hasTable('academic_periods')) {
            return new self([
                'tahun_ajaran' => '2026/2027',
                'semester'     => 'ganjil',
                'is_active'    => true,
            ]);
        }

        $active = static::where('is_active', true)->first();
        if ($active) {
            return $active;
        }

        $latest = static::orderByDesc('tahun_ajaran')
            ->orderByRaw("CASE WHEN semester = 'genap' THEN 2 ELSE 1 END DESC")
            ->first();

        if ($latest) {
            $latest->update(['is_active' => true]);
            return $latest;
        }

        return static::create([
            'tahun_ajaran'    => '2026/2027',
            'semester'        => 'ganjil',
            'tanggal_mulai'   => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_active'       => true,
        ]);
    }

    public static function currentId(): ?int
    {
        return static::current()->id;
    }

    public static function activeTahunAjaran(): string
    {
        return static::current()->tahun_ajaran;
    }

    public static function activeSemester(): string
    {
        return static::current()->semester;
    }

    /**
     * Tahun awal dari tahun ajaran aktif (contoh: "2026/2027" -> 2026).
     */
    public static function defaultAngkatan(): int
    {
        $ta = static::activeTahunAjaran();
        if (preg_match('/^(\d{4})/', $ta, $matches)) {
            return (int) $matches[1];
        }

        return (int) now()->format('Y');
    }
}
