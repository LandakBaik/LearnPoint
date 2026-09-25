<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    use HasFactory;

    protected $table = 'materis';

    protected $fillable = [
        'judul',
        'file_materi',
        'pengampu_kelas_id',
    ];

    public function pengampuKelas()
    {
        return $this->belongsTo(PengampuKelas::class, 'pengampu_kelas_id');
    }

    public function getGuruMapelAttribute()
    {
        return $this->pengampuKelas?->guruMapel;
    }
}
