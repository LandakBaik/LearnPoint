<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Soal $soal): void {
            $activityForeignKeys = [$soal->quiz_id, $soal->tugas_id, $soal->ujian_id];

            if (count(array_filter($activityForeignKeys, static fn ($value) => $value !== null)) !== 1) {
                throw new \InvalidArgumentException('Soal harus terhubung tepat ke satu aktivitas.');
            }
        });
    }

    protected $table = 'soals';

    protected $fillable = [
        'pertanyaan',
        'pilihan',
        'kunci_jawaban',
        'quiz_id',
        'tugas_id',
        'ujian_id',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function menjawabs()
    {
        return $this->hasMany(Menjawab::class, 'soal_id');
    }
}
