<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilais';

    protected $fillable = [
        'nilai',
        'status',
        'durasi',
        'file_jawaban',
        'siswa_id',
        'tugas_id',
        'quiz_id',
        'seleksi_ujian_id',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function seleksiUjian()
    {
        return $this->belongsTo(SeleksiUjian::class, 'seleksi_ujian_id');
    }
}
