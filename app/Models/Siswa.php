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
        'status',
    ];

    public function kelas()
    {
        return $this->hasOneThrough(
            Kelas::class,
            AnggotaKelas::class,
            'siswa_id',
            'id',
            'id',
            'kelas_id'
        )
            ->where('anggota_kelases.tahun_ajaran', '2026/2027')
            ->where('anggota_kelases.semester', 'ganjil');
    }

    public function anggotaKelases()
    {
        return $this->hasMany(AnggotaKelas::class, 'siswa_id');
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
