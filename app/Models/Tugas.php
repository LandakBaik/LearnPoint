<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugases';

    protected $fillable = [
        'judul',
        'deadline',
        'tipe',
        'pengampu_kelas_id',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
        ];
    }

    public function pengampuKelas()
    {
        return $this->belongsTo(PengampuKelas::class, 'pengampu_kelas_id');
    }

    public function getGuruMapelAttribute()
    {
        return $this->pengampuKelas?->guruMapel;
    }

    public function soals()
    {
        return $this->hasMany(Soal::class, 'tugas_id');
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class, 'tugas_id');
    }
}
