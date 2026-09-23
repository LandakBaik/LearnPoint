<?php

namespace App\Models;

use Database\Factories\KelasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    /** @use HasFactory<KelasFactory> */
    use HasFactory;

    protected $table = 'kelases';

    protected $fillable = [
        'nama_kelas',
        'tingkatan',
        'guru_id',
        'jadwal',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }

    public function anggotaKelases(): HasMany
    {
        return $this->hasMany(AnggotaKelas::class, 'kelas_id');
    }

    public function guruMapels(): HasMany
    {
        return $this->hasMany(GuruMapel::class, 'kelas_id');
    }

    /**
     * Get the current active schedule image URL for this class
     */
    public function getJadwalUrlAttribute(): ?string
    {
        return $this->jadwal ? asset('storage/' . $this->jadwal) : null;
    }
}