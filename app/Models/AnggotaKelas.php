<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaKelas extends Model
{
    use HasFactory;

    protected $table = 'anggota_kelases';

    protected $fillable = [
        'kelas_id',
        'siswa_id',
        'academic_period_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->academic_period_id)) {
                $model->academic_period_id = AcademicPeriod::currentId();
            }
        });
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }
}
