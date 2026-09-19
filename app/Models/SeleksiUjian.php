<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeleksiUjian extends Model
{
    use HasFactory;

    protected $table = 'seleksi_ujians';

    protected $fillable = [
        'nilai_awal',
        'jumlah_remidi',
        'nilai_akhir',
        'siswa_id',
        'ujian_id',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class, 'seleksi_ujian_id');
    }
}
