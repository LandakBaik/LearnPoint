<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuruMapel extends Model
{
    use HasFactory;

    protected $table = 'guru_mapels';

    protected $fillable = [
        'guru_id',
        'mapel_id',
        'jadwal',
    ];

    public function getJadwalUrlAttribute(): ?string
    {
        return $this->jadwal ? asset('storage/' . $this->jadwal) : null;
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function pengampuKelases()
    {
        return $this->hasMany(PengampuKelas::class, 'guru_mapel_id');
    }

    public function kelases()
    {
        return $this->belongsToMany(Kelas::class, 'pengampu_kelas', 'guru_mapel_id', 'kelas_id');
    }
}
