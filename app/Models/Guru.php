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
        'status',
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

    public function pengampuKelases()
    {
        return $this->hasManyThrough(PengampuKelas::class, GuruMapel::class, 'guru_id', 'guru_mapel_id');
    }

    public function getJadwalAttribute(): ?string
    {
        return $this->guruMapels()
            ->has('pengampuKelases')
            ->whereNotNull('jadwal')
            ->latest('updated_at')
            ->value('jadwal');
    }

    public function getJadwalUrlAttribute(): ?string
    {
        $jadwal = $this->jadwal;
        return $jadwal ? asset('storage/' . $jadwal) : null;
    }

    /**
     * Tanggal lahir yang diformat (misal: 15 Maret 1988) dari 8 digit pertama NIP.
     */
    public function getTanggalLahirAttribute(): ?string
    {
        return self::extractTanggalLahirFromNip($this->nip);
    }

    /**
     * Tanggal lahir format Y-m-d dari 8 digit pertama NIP.
     */
    public function getTanggalLahirRawAttribute(): ?string
    {
        $carbon = self::parseTanggalLahirCarbon($this->nip);
        return $carbon ? $carbon->format('Y-m-d') : null;
    }

    /**
     * Instance Carbon tanggal lahir dari 8 digit pertama NIP.
     */
    public function getTanggalLahirCarbonAttribute(): ?\Carbon\Carbon
    {
        return self::parseTanggalLahirCarbon($this->nip);
    }

    /**
     * Jenis kelamin dari 1 digit bagian ketiga NIP (digit ke-15).
     * Kode: 1 = Laki-laki, 2 = Perempuan.
     */
    public function getJenisKelaminAttribute(): ?string
    {
        return self::extractJenisKelaminFromNip($this->nip);
    }

    /**
     * Static helper untuk ekstrak tanggal lahir dari 8 digit pertama NIP.
     * Format default: 'd F Y' (Indonesia).
     */
    public static function extractTanggalLahirFromNip(?string $nip, string $format = 'd F Y'): ?string
    {
        $carbon = self::parseTanggalLahirCarbon($nip);
        if (!$carbon) {
            return null;
        }

        return $carbon->locale('id')->translatedFormat($format);
    }

    /**
     * Static helper untuk parse Carbon dari 8 digit pertama NIP (YYYYMMDD).
     */
    public static function parseTanggalLahirCarbon(?string $nip): ?\Carbon\Carbon
    {
        if (empty($nip)) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $nip);
        if (strlen($digits) < 8) {
            return null;
        }

        $year = (int) substr($digits, 0, 4);
        $month = (int) substr($digits, 4, 2);
        $day = (int) substr($digits, 6, 2);

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromDate($year, $month, $day);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Static helper untuk ekstrak jenis kelamin dari 1 digit bagian ketiga NIP (digit ke-15).
     * 1 = Laki-laki, 2 = Perempuan.
     */
    public static function extractJenisKelaminFromNip(?string $nip): ?string
    {
        if (empty($nip)) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $nip);
        if (strlen($digits) < 15) {
            return null;
        }

        $code = substr($digits, 14, 1);
        if ($code === '1') {
            return 'Laki-laki';
        } elseif ($code === '2') {
            return 'Perempuan';
        }

        return null;
    }
}

