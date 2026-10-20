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
        'deskripsi',
        'file_materi',
        'url_youtube',
        'pengampu_kelas_id',
        'id_grub_materi',
    ];

    public function pengampuKelas()
    {
        return $this->belongsTo(PengampuKelas::class, 'pengampu_kelas_id');
<<<<<<< HEAD
=======
    }

    public function getGuruMapelAttribute()
    {
        return $this->pengampuKelas?->guruMapel;
>>>>>>> c9d58f8240fac8d35441036e3d07b227ec12d654
    }
}
