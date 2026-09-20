<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswas';

    protected $fillable = [
        'nama_siswa',
        'nis',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'wali_murid',
        'nohp_wali',
        'kelas_id',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'siswa_id');
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'siswa_id');
    }

    public function menjawabs()
    {
        return $this->hasMany(Menjawab::class, 'siswa_id');
    }

    public function seleksiUjians()
    {
        return $this->hasMany(SeleksiUjian::class, 'siswa_id');
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class, 'siswa_id');
    }
}
