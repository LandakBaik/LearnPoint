<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menjawab extends Model
{
    use HasFactory;

    protected $table = 'menjawabs';

    protected $fillable = [
        'soal_id',
        'siswa_id',
        'power_id',
        'jawaban_dipilih',
        'benar',
        'poin_diperoleh',
        'koin_diklaim',
    ];

    protected function casts(): array
    {
        return [
            'benar' => 'boolean',
            'poin_diperoleh' => 'decimal:2',
            'koin_diklaim' => 'boolean',
        ];
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
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
     * Menghitung poin bersih yang diperoleh siswa pada satu butir soal
     * berdasarkan bobot dasar, kebenaran jawaban, dan Power yang digunakan.
     *
     * Rumus Sesuai Spesifikasi Teknis:
     * 1. Tanpa Power (atau lucky_clover / remove_wrong_answer / second_chance):
     *    - Benar: bobot
     *    - Salah: 0
     * 2. Menggunakan two_x_score:
     *    - Benar: bobot * 2
     *    - Salah: 0
     * 3. Menggunakan double_side_knife:
     *    - Benar: bobot * 2
     *    - Salah: -(bobot * 0.5)
     */
    public static function hitungPoin(float $bobot, bool $isBenar, ?Power $power = null): float
    {
        $bobotDasar = $bobot > 0 ? $bobot : 10.0;
        $kodePower = $power?->kode;

        if ($kodePower === 'two_x_score') {
            return $isBenar ? round($bobotDasar * 2, 2) : 0.0;
        }

        if ($kodePower === 'double_side_knife') {
            return $isBenar ? round($bobotDasar * 2, 2) : round(-($bobotDasar * 0.5), 2);
        }

        return $isBenar ? round($bobotDasar, 2) : 0.0;
    }
}
