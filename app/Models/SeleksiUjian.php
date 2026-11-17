<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeleksiUjian extends Model
{
    use HasFactory;

    protected $table = 'seleksi_ujians';

    protected $fillable = [
    'ujian_id',
    'siswa_id',
    'nilai_awal',
    'nilai_akhir',
    'jumlah_remidi',
    'waktu_mulai',
    'waktu_selesai',
    'status',
    'pelanggaran_count',
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
