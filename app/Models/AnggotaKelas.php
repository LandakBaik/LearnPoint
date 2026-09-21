<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaKelas extends Model
{
    use HasFactory;

    protected $table = 'anggota_kelases';

    protected $fillable = [
        'kelas_id',
        'siswa_id',
        'tahun_ajaran',
        'semester',
        'jadwal',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Helper to get full storage URL for jadwal image
     */
    public function getJadwalUrlAttribute(): ?string
    {
        return $this->jadwal ? asset('storage/' . $this->jadwal) : null;
    }
}
