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
        'kelas_id',
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

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tugases()
    {
        return $this->hasMany(Tugas::class, 'guru_mapel_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'guru_mapel_id');
    }

    public function ujians()
    {
        return $this->hasMany(Ujian::class, 'guru_mapel_id');
    }

    public function materis()
    {
        return $this->hasMany(Materi::class, 'guru_mapel_id');
    }
}
