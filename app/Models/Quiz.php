<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $table = 'quizzes';

    protected $fillable = [
        'judul',
        'level',
        'kesulitan',
        'pengampu_kelas_id',
    ];

    public function pengampuKelas()
    {
        return $this->belongsTo(PengampuKelas::class, 'pengampu_kelas_id');
    }

    public function getGuruMapelAttribute()
    {
        return $this->pengampuKelas?->guruMapel;
    }

    public function soals()
    {
        return $this->hasMany(Soal::class, 'quiz_id');
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class, 'quiz_id');
    }
}
