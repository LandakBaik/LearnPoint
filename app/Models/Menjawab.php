<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menjawab extends Model
{
    use HasFactory;

    protected $table = 'menjawabs';

    protected $fillable = [
        'soal_id',
        'siswa_id',
        'jawaban_dipilih',
        'benar',
    ];

    protected function casts(): array
    {
        return [
            'benar' => 'boolean',
        ];
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
