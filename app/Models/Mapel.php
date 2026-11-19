<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'mapels';

    public const TIPE_MAPEL_OPTIONS = [
        'teori'             => 'Teori',
        'praktik'           => 'Praktik',
        'teori_dan_praktik' => 'Teori dan Praktik',
        'agama_islam'       => 'Agama Islam',
        'agama_kristen'     => 'Agama Kristen',
        'agama_katolik'     => 'Agama Katolik',
        'agama_hindu'       => 'Agama Hindu',
        'agama_buddha'      => 'Agama Buddha',
        'agama_konghucu'    => 'Agama Konghucu',
    ];

    public const TIPE_AGAMA_MAP = [
        'agama_islam'    => 'Islam',
        'agama_kristen'  => 'Kristen',
        'agama_katolik'  => 'Katolik',
        'agama_hindu'    => 'Hindu',
        'agama_buddha'   => 'Buddha',
        'agama_konghucu' => 'Konghucu',
    ];

    protected $fillable = [
        'nama_mapel',
        'kkm',
        'tipe_mapel',
    ];

    public function getTargetAgamaAttribute(): ?string
    {
        return self::TIPE_AGAMA_MAP[$this->tipe_mapel] ?? null;
    }

    public function getTipeLabelAttribute(): string
    {
        return self::TIPE_MAPEL_OPTIONS[$this->tipe_mapel] ?? ucfirst((string) ($this->tipe_mapel ?: 'teori'));
    }

    public static function tipeFromAgama(?string $agama): ?string
    {
        if (blank($agama)) {
            return null;
        }

        foreach (self::TIPE_AGAMA_MAP as $tipe => $namaAgama) {
            if (strcasecmp(trim($namaAgama), trim($agama)) === 0) {
                return $tipe;
            }
        }

        return null;
    }

    /**
     * Filter mapel agar hanya menampilkan mapel non-agama (teori/praktik) atau mapel agama yang sesuai agama siswa.
     */
    public function scopeForSiswa($query, ?Siswa $siswa)
    {
        $agamaTipes = array_keys(self::TIPE_AGAMA_MAP);
        $siswaTipeAgama = self::tipeFromAgama($siswa?->agama);

        return $query->where(function ($q) use ($agamaTipes, $siswaTipeAgama) {
            $q->whereNull('tipe_mapel')
              ->orWhereNotIn('tipe_mapel', $agamaTipes);

            if ($siswaTipeAgama !== null) {
                $q->orWhere('tipe_mapel', $siswaTipeAgama);
            }
        });
    }

    /**
     * Cek apakah mapel ini boleh diakses oleh siswa tertentu berdasarkan tipe mapel & agamanya.
     */
    public function isAccessibleBySiswa(?Siswa $siswa): bool
    {
        $targetAgama = $this->target_agama;

        if (blank($targetAgama)) {
            return true;
        }

        if (!$siswa || blank($siswa->agama)) {
            return false;
        }

        return strcasecmp(trim($targetAgama), trim($siswa->agama)) === 0;
    }

    public function guruMapels()
    {
        return $this->hasMany(GuruMapel::class, 'mapel_id');
    }

    public function pengampuKelases()
    {
        return $this->hasManyThrough(PengampuKelas::class, GuruMapel::class, 'mapel_id', 'guru_mapel_id');
    }
}
