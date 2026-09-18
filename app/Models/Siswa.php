<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'nama_siswa',
        'nis',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'wali_murid',
        'nohp_wali',
    ];
    //
}
