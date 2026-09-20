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
        'guru_mapel_id',
    ];

    public function guruMapel()
    {
        return $this->belongsTo(GuruMapel::class, 'guru_mapel_id');
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
