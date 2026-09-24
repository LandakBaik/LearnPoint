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
        'nip',
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

    /**
     * Get the latest schedule photo for this teacher from their teaching assignments
     */
    public function getJadwalAttribute(): ?string
    {
        return $this->guruMapels()->whereNotNull('jadwal')->latest()->value('jadwal');
    }

    public function getJadwalUrlAttribute(): ?string
    {
        $jadwal = $this->jadwal;
        return $jadwal ? asset('storage/' . $jadwal) : null;
    }
}

