<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengampuKelas extends Model
{
    use HasFactory;

    protected $table = 'pengampu_kelas';

    protected $fillable = [
        'guru_mapel_id',
        'kelas_id',
    ];

    protected static function booted()
    {
        static::saving(function ($pengampuKelas) {
            $mapelId = $pengampuKelas->guruMapel?->mapel_id 
                ?? GuruMapel::where('id', $pengampuKelas->guru_mapel_id)->value('mapel_id');

            if ($mapelId && $pengampuKelas->kelas_id) {
                $query = static::where('kelas_id', $pengampuKelas->kelas_id)
                    ->whereHas('guruMapel', function ($q) use ($mapelId) {
                        $q->where('mapel_id', $mapelId);
                    });

                if ($pengampuKelas->exists) {
                    $query->where('id', '!=', $pengampuKelas->id);
                }

                if ($query->exists()) {
                    throw new \InvalidArgumentException("Mata pelajaran ini sudah memiliki guru pengampu di kelas tersebut. Hanya diperbolehkan 1 guru untuk setiap mata pelajaran dalam satu kelas.");
                }
            }
        });
    }

    public function guruMapel()
    {
        return $this->belongsTo(GuruMapel::class, 'guru_mapel_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // Accessors to simplify accessing teacher and subject directly from pengampu_kelas
    public function getGuruAttribute()
    {
        return $this->guruMapel?->guru;
    }

    public function getMapelAttribute()
    {
        return $this->guruMapel?->mapel;
    }

    public function tugases()
    {
        return $this->hasMany(Tugas::class, 'pengampu_kelas_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'pengampu_kelas_id');
    }

    public function ujians()
    {
        return $this->hasMany(Ujian::class, 'pengampu_kelas_id');
    }

    public function materis()
    {
        return $this->hasMany(Materi::class, 'pengampu_kelas_id');
    }
}
