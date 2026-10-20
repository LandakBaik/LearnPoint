<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'mapels';

    protected $fillable = [
        'nama_mapel',
        'kkm',
    ];

    public function guruMapels()
    {
        return $this->hasMany(GuruMapel::class, 'mapel_id');
    }

    public function pengampuKelases()
    {
        return $this->hasManyThrough(PengampuKelas::class, GuruMapel::class, 'mapel_id', 'guru_mapel_id');
    }
}
