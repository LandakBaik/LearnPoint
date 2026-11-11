<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Power extends Model
{
    use HasFactory;

    protected $table = 'powers';

    protected $fillable = [
        'kode',
        'nama',
        'tagline',
        'deskripsi',
        'detail_efek',
        'tipe_efek',
        'stok_awal',
        'icon',
        'tema_warna',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stok_awal' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Katalog 5 Item Power sesuai Spesifikasi Teknis LearnPoint.
     */
    public const DEFAULT_CATALOG = [
        [
            'kode' => 'double_side_knife',
            'nama' => 'Double Side Knife',
            'tagline' => '2X Skor / Risiko Penalti',
            'deskripsi' => 'Menggandakan skor soal menjadi 2x lipat jika jawabanmu benar, tetapi mengurangi poin sebesar 50% bobot soal jika jawabanmu salah.',
            'detail_efek' => 'Jawaban Benar memperoleh 2x Poin Soal (+100%). Jawaban Salah terkena penalti -50% Poin Soal.',
            'tipe_efek' => 'multiplier',
            'stok_awal' => 2,
            'icon' => 'fa-solid fa-khanda',
            'tema_warna' => 'rose',
            'is_active' => true,
        ],
        [
            'kode' => 'second_chance',
            'nama' => 'Second Chance',
            'tagline' => 'Kesempatan Kedua',
            'deskripsi' => 'Memberikan perisai kesempatan kedua ketika kamu memilih jawaban yang salah pada soal ini sebelum soal dinyatakan selesai.',
            'detail_efek' => 'Menahan 1x pilihan jawaban yang salah secara otomatis dan mengizinkanmu memilih ulang jawaban yang benar.',
            'tipe_efek' => 'second_chance',
            'stok_awal' => 2,
            'icon' => 'fa-solid fa-shield-heart',
            'tema_warna' => 'emerald',
            'is_active' => true,
        ],
        [
            'kode' => 'lucky_clover',
            'nama' => 'Lucky Clover',
            'tagline' => 'Petunjuk Soal (Hint)',
            'deskripsi' => 'Membuka kartu petunjuk (Hint) analisis konsep pada soal yang sedang dibuka untuk membantu menemukan jawaban yang tepat.',
            'detail_efek' => 'Menampilkan kartu petunjuk (Hint) langsung di bawah pertanyaan soal ini.',
            'tipe_efek' => 'hint',
            'stok_awal' => 2,
            'icon' => 'fa-solid fa-clover',
            'tema_warna' => 'teal',
            'is_active' => true,
        ],
        [
            'kode' => 'two_x_score',
            'nama' => '2X Score',
            'tagline' => 'Pengganda Skor 2X',
            'deskripsi' => 'Menggandakan perolehan skor menjadi 2x lipat apabila kamu menjawab soal ini dengan benar tanpa risiko penalti.',
            'detail_efek' => 'Jawaban Benar memperoleh 2x Poin Soal (+100%). Jawaban Salah memperoleh 0 Poin (tanpa penalti).',
            'tipe_efek' => 'multiplier',
            'stok_awal' => 2,
            'icon' => 'fa-solid fa-bolt',
            'tema_warna' => 'amber',
            'is_active' => true,
        ],
        [
            'kode' => 'remove_wrong_answer',
            'nama' => 'Remove Wrong Answer',
            'tagline' => 'Eliminasi Opsi Salah',
            'deskripsi' => 'Menghilangkan hingga 2 pilihan jawaban yang salah secara otomatis sehingga menyisakan opsi yang memungkinkanmu menjawab dengan benar.',
            'detail_efek' => 'Mengeliminasi (menonaktifkan) hingga 2 pilihan jawaban yang salah secara otomatis pada soal ini.',
            'tipe_efek' => 'eliminate_options',
            'stok_awal' => 2,
            'icon' => 'fa-solid fa-filter-circle-xmark',
            'tema_warna' => 'indigo',
            'is_active' => true,
        ],
    ];

    public function siswaPowers()
    {
        return $this->hasMany(SiswaPower::class, 'power_id');
    }

    public function menjawabs()
    {
        return $this->hasMany(Menjawab::class, 'power_id');
    }

    /**
     * Pastikan katalog default Power tersedia di tabel powers.
     */
    public static function ensureDefaultCatalog(): void
    {
        if (static::count() > 0) {
            return;
        }

        foreach (self::DEFAULT_CATALOG as $item) {
            static::firstOrCreate(
                ['kode' => $item['kode']],
                $item
            );
        }
    }

    /**
     * Helper class styling UI berdasarkan tema_warna.
     */
    public function getUiClassesAttribute(): array
    {
        return match ($this->tema_warna) {
            'rose' => [
                'icon_bg' => 'bg-rose-50 text-rose-600 border-rose-200',
                'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
                'active_pill' => 'bg-rose-50 text-rose-700 border-rose-200',
            ],
            'emerald' => [
                'icon_bg' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'active_pill' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            'teal' => [
                'icon_bg' => 'bg-teal-50 text-teal-600 border-teal-200',
                'badge' => 'bg-teal-50 text-teal-700 border-teal-200',
                'active_pill' => 'bg-teal-50 text-teal-700 border-teal-200',
            ],
            'amber' => [
                'icon_bg' => 'bg-amber-50 text-amber-600 border-amber-200',
                'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                'active_pill' => 'bg-amber-50 text-amber-700 border-amber-200',
            ],
            'indigo' => [
                'icon_bg' => 'bg-indigo-50 text-indigo-600 border-indigo-200',
                'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'active_pill' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            ],
            default => [
                'icon_bg' => 'bg-blue-50 text-blue-600 border-blue-200',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                'active_pill' => 'bg-blue-50 text-blue-700 border-blue-200',
            ],
        };
    }
}

