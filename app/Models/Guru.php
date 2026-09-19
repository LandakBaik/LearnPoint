<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'gurus';

    protected $fillable = [
        'nama',
        'nip',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'guru_id');
    }

    public function kelas()
    {
        return $this->hasOne(Kelas::class, 'guru_id');
    }

    public function guruMapels()
    {
        return $this->hasMany(GuruMapel::class, 'guru_id');
    }
}
