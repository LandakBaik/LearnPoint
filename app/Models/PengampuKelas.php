<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengampuKelas extends Model
{
    use HasFactory;

    protected $table = 'pengampu_kelas';

    protected $fillable = [
        'guru_mapel_id',
        'kelas_id',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function guruMapel()
    {
        return $this->belongsTo(GuruMapel::class, 'guru_mapel_id');
    }

    public function tugases()
    {
        return $this->hasMany(Tugas::class, 'pengampu_kelas_id');
    }
}