<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Power
 * 
 * Subclass / Adapter dari Model Item yang mewakili item dengan kategori 'power'.
 * Menjaga backward-compatibility 100% untuk seluruh modul kuis, inventori, dan view.
 */
class Power extends Item
{
    use HasFactory;

    protected $table = 'items';

    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('kategoriPower', function (Builder $builder) {
            $builder->where('items.kategori', 'power');
        });

        static::creating(function ($model) {
            $model->kategori = 'power';
            $model->is_stackable = true;
        });
    }

    /**
     * Backward-compatibility relasi ke inventori siswa.
     */
    public function siswaPowers()
    {
        return $this->hasMany(Inventory::class, 'item_id');
    }

    /**
     * Relasi ke jawaban kuis siswa.
     */
    public function menjawabs()
    {
        return $this->hasMany(Menjawab::class, 'power_id');
    }

    /**
     * Pastikan katalog default Power tersedia (memanggil Item::ensureDefaultCatalog).
     */
    public static function ensureDefaultCatalog(): void
    {
        Item::ensureDefaultCatalog();

        // Pastikan tabel powers lama juga tersinkronisasi jika tabelnya masih ada
        if (\Illuminate\Support\Facades\Schema::hasTable('powers')) {
            foreach (self::DEFAULT_CATALOG as $item) {
                \Illuminate\Support\Facades\DB::table('powers')->updateOrInsert(
                    ['kode' => $item['kode']],
                    array_merge($item, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }
    }

    public const DEFAULT_CATALOG = [
        [
            'id' => 1,
            'kode' => 'double_side_knife',
            'nama' => 'Double Side Knife',
            'tagline' => '2X Skor / Risiko Penalti',
            'deskripsi' => 'Menggandakan skor soal menjadi 2x lipat jika jawabanmu benar, tetapi mengurangi poin sebesar 50% bobot soal jika jawabanmu salah.',
            'detail_efek' => 'Jawaban Benar memperoleh 2x Poin Soal (+100%). Jawaban Salah terkena penalti -50% Poin Soal.',
            'tipe_efek' => 'multiplier',
            'rarity' => 'rare',
            'bobot_gacha' => 15,
            'stok_awal' => 2,
            'icon' => 'fa-solid fa-khanda',
            'tema_warna' => 'rose',
            'is_active' => true,
        ],
        [
            'id' => 2,
            'kode' => 'second_chance',
            'nama' => 'Second Chance',
            'tagline' => 'Kesempatan Kedua',
            'deskripsi' => 'Memberikan perisai kesempatan kedua ketika kamu memilih jawaban yang salah pada soal ini sebelum soal dinyatakan selesai.',
            'detail_efek' => 'Menahan 1x pilihan jawaban yang salah secara otomatis dan mengizinkanmu memilih ulang jawaban yang benar.',
            'tipe_efek' => 'second_chance',
            'rarity' => 'rare',
            'bobot_gacha' => 20,
            'stok_awal' => 2,
            'icon' => 'fa-solid fa-shield-heart',
            'tema_warna' => 'emerald',
            'is_active' => true,
        ],
        [
            'id' => 3,
            'kode' => 'lucky_clover',
            'nama' => 'Lucky Clover',
            'tagline' => 'Petunjuk Soal (Hint)',
            'deskripsi' => 'Membuka kartu petunjuk (Hint) analisis konsep pada soal yang sedang dibuka untuk membantu menemukan jawaban yang tepat.',
            'detail_efek' => 'Menampilkan kartu petunjuk (Hint) langsung di bawah pertanyaan soal ini.',
            'tipe_efek' => 'hint',
            'rarity' => 'common',
            'bobot_gacha' => 30,
            'stok_awal' => 2,
            'icon' => 'fa-solid fa-clover',
            'tema_warna' => 'teal',
            'is_active' => true,
        ],
        [
            'id' => 4,
            'kode' => 'two_x_score',
            'nama' => '2X Score',
            'tagline' => 'Pengganda Skor 2X',
            'deskripsi' => 'Menggandakan perolehan skor menjadi 2x lipat apabila kamu menjawab soal ini dengan benar tanpa risiko penalti.',
            'detail_efek' => 'Jawaban Benar memperoleh 2x Poin Soal (+100%). Jawaban Salah memperoleh 0 Poin (tanpa penalti).',
            'tipe_efek' => 'multiplier',
            'rarity' => 'epic',
            'bobot_gacha' => 10,
            'stok_awal' => 2,
            'icon' => 'fa-solid fa-bolt',
            'tema_warna' => 'amber',
            'is_active' => true,
        ],
        [
            'id' => 5,
            'kode' => 'remove_wrong_answer',
            'nama' => 'Remove Wrong Answer',
            'tagline' => 'Eliminasi Opsi Salah',
            'deskripsi' => 'Menghilangkan hingga 2 pilihan jawaban yang salah secara otomatis sehingga menyisakan opsi yang memungkinkanmu menjawab dengan benar.',
            'detail_efek' => 'Mengeliminasi (menonaktifkan) hingga 2 pilihan jawaban yang salah secara otomatis pada soal ini.',
            'tipe_efek' => 'eliminate_options',
            'rarity' => 'common',
            'bobot_gacha' => 25,
            'stok_awal' => 2,
            'icon' => 'fa-solid fa-filter-circle-xmark',
            'tema_warna' => 'indigo',
            'is_active' => true,
        ],
    ];
}
