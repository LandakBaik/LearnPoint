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

    /**
     * Daftar pilihan Asset Tema Planet Kuis (cocok 1:1 dengan index array `QuizAssets.planetGroups` di Flutter).
     */
    public const PLANET_ASSET_OPTIONS = [
        0 => [
            'index'       => 0,
            'id'          => 'planet_1_chess',
            'nama'        => 'Planet 1 — Chess Kingdom',
            'deskripsi'   => 'Tema catur fantasi dengan 3 variasi peta level (Teal, Olive, Ruins)',
            'badge_class' => 'bg-cyan-50 border-cyan-200/80 text-cyan-700',
            'scenes'      => [
                0 => 'Scene 1: Chess Teal (Biru Teal)',
                1 => 'Scene 2: Chess Olive (Hijau Zaitun & Pedang)',
                2 => 'Scene 3: Chess Ruins (Reruntuhan Emas)',
            ],
        ],
        1 => [
            'index'       => 1,
            'id'          => 'planet_2_void',
            'nama'        => 'Planet 2 — Void Dimension',
            'deskripsi'   => 'Tema dimensi void ungu & gerbang reruntuhan kuno',
            'badge_class' => 'bg-purple-50 border-purple-200/80 text-purple-700',
            'scenes'      => [
                0 => 'Scene 1: Void Ruin (Gerbang Void & Pasir Jingga)',
            ],
        ],
        2 => [
            'index'       => 2,
            'id'          => 'planet_3_ribbon',
            'nama'        => 'Planet 3 — Ribbon Castle',
            'deskripsi'   => 'Tema kastil langit, biola, dan pita merah',
            'badge_class' => 'bg-rose-50 border-rose-200/80 text-rose-700',
            'scenes'      => [
                0 => 'Scene 1: Ribbon Castle (Kastil & Pita Merah)',
            ],
        ],
        3 => [
            'index'       => 3,
            'id'          => 'planet_4_sun',
            'nama'        => 'Planet 4 — Solar Flare',
            'deskripsi'   => 'Tema planet matahari membara',
            'badge_class' => 'bg-amber-50 border-amber-200/80 text-amber-700',
            'scenes'      => [
                0 => 'Scene 1: Solar Ruins (Default Scene)',
            ],
        ],
    ];

    protected $fillable = [
        'nama_mapel',
        'kkm',
        'tipe_mapel',
        'asset',
    ];

    protected function casts(): array
    {
        return [
            'asset' => 'integer',
        ];
    }

    public function getTargetAgamaAttribute(): ?string
    {
        return self::TIPE_AGAMA_MAP[$this->tipe_mapel] ?? null;
    }

    public function getTipeLabelAttribute(): string
    {
        return self::TIPE_MAPEL_OPTIONS[$this->tipe_mapel] ?? ucfirst((string) ($this->tipe_mapel ?: 'teori'));
    }

    public function getPlanetInfoAttribute(): array
    {
        $idx = (int) ($this->asset ?? 0);
        return self::PLANET_ASSET_OPTIONS[$idx] ?? self::PLANET_ASSET_OPTIONS[0];
    }

    public function getPlanetLabelAttribute(): string
    {
        return $this->planet_info['nama'] ?? 'Planet 1 — Chess Kingdom';
    }

    public function getPlanetScenesAttribute(): array
    {
        return $this->planet_info['scenes'] ?? [0 => 'Scene 1 (Default)'];
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
