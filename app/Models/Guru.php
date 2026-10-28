<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'gurus';

    protected $fillable = [
        'nama',
        'nuptk',
        'nomor_telepon',
        'jenis_kelamin',
        'status',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'guru_id');
    }

    public function kelas()
    {
        return $this->hasOne(Kelas::class, 'guru_id');
    }

    public function guruMapels()
    {
        return $this->hasMany(GuruMapel::class, 'guru_id');
    }

    public function pengampuKelases()
    {
        return $this->hasManyThrough(PengampuKelas::class, GuruMapel::class, 'guru_id', 'guru_mapel_id');
    }

    public function getJadwalAttribute(): ?string
    {
        return $this->guruMapels()
            ->whereNotNull('jadwal')
            ->latest('updated_at')
            ->value('jadwal');
    }

    public function getJadwalUrlAttribute(): ?string
    {
        $jadwal = $this->jadwal;
        return $jadwal ? asset('storage/' . $jadwal) : null;
    }

    /**
     * Alias backward compatibility untuk no_telp.
     */
    public function getNoTelpAttribute(): ?string
    {
        return $this->nomor_telepon;
    }

    /**
     * Alias backward compatibility untuk NIP -> NUPTK.
     */
    public function getNipAttribute(): ?string
    {
        return $this->nuptk;
    }
}
