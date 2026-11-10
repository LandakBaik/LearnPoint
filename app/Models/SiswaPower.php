<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiswaPower extends Model
{
    use HasFactory;

    protected $table = 'siswa_powers';

    protected $fillable = [
        'siswa_id',
        'power_id',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function power()
    {
        return $this->belongsTo(Power::class, 'power_id');
    }

    /**
     * Inisialisasi inventori awal Power untuk siswa berdasarkan stok_awal master Power
     * apabila siswa belum memiliki record inventori sama sekali.
     */
    public static function initializeDefaultInventory(Siswa $siswa): void
    {
        Power::ensureDefaultCatalog();

        if (static::where('siswa_id', $siswa->id)->exists()) {
            return;
        }

        $powers = Power::where('is_active', true)->get();
        foreach ($powers as $power) {
            static::firstOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'power_id' => $power->id,
                ],
                [
                    'jumlah' => max(0, (int) $power->stok_awal),
                ]
            );
        }
    }
}

