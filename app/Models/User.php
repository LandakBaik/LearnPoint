<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'guru_id',
        'siswa_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'user_id');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'user_id');
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isKepalaSekolah(): bool
    {
        return $this->role === 'kepala_sekolah';
    }

    public function isAktif(): bool
    {
        return ($this->status ?? 'aktif') === 'aktif';
    }

    public function getNuptkAttribute(): ?string
    {
        if ($this->isGuru()) {
            return $this->guru?->nuptk ?? $this->username;
        }
        return null;
    }

    public function getNipAttribute(): ?string
    {
        return $this->nuptk;
    }

    public function getTanggalLahirAttribute(): ?string
    {
        if ($this->isSiswa() && $this->siswa && $this->siswa->tanggal_lahir) {
            try {
                return \Carbon\Carbon::parse($this->siswa->tanggal_lahir)->locale('id')->translatedFormat('d F Y');
            } catch (\Throwable $e) {
                return $this->siswa->tanggal_lahir;
            }
        }

        return null;
    }

    public function getBirthdateAttribute(): ?string
    {
        return $this->tanggal_lahir;
    }

    public function getJenisKelaminAttribute(): ?string
    {
        if ($this->isGuru()) {
            return $this->guru?->jenis_kelamin;
        }

        if ($this->isSiswa() && $this->siswa) {
            return $this->siswa->jenis_kelamin;
        }

        return null;
    }

    public function getGenderAttribute(): ?string
    {
        return $this->jenis_kelamin;
    }

    public function getNomorTeleponAttribute(): ?string
    {
        if ($this->isGuru()) {
            return $this->guru?->nomor_telepon;
        }

        return null;
    }
}
