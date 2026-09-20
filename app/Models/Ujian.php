<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $table = 'ujians';

    protected $fillable = [
        'judul',
        'deskripsi',
        'waktu_mulai',
        'deadline',
        'durasi_menit',
        'guru_mapel_id',
    ];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'deadline' => 'datetime',
        ];
    }

    public function guruMapel()
    {
        return $this->belongsTo(GuruMapel::class, 'guru_mapel_id');
    }

    public function soals()
    {
        return $this->hasMany(Soal::class, 'ujian_id');
    }

    public function seleksiUjians()
    {
        return $this->hasMany(SeleksiUjian::class, 'ujian_id');
    }
}
