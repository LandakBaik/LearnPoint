<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'deskripsi',
        'rarity',
        'bobot_gacha',
        'is_stackable',
        'asset_url',
        'atribut',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_stackable' => 'boolean',
            'is_active'    => 'boolean',
            'bobot_gacha'  => 'integer',
            'atribut'      => 'array',
        ];
    }

    public const DEFAULT_ITEMS = [
        // === POWERS (Kategori: 'power', stackable: true) ===
        [
            'id' => 1,
            'kode' => 'double_side_knife',
            'nama' => 'Double Side Knife',
            'kategori' => 'power',
            'deskripsi' => 'Jika jawaban benar, kamu mendapat 2.5x poin normal. Namun jika jawaban salah, kamu mendapat pengurangan -0.5x poin.',
            'rarity' => 'rare',
            'bobot_gacha' => 20,
            'is_stackable' => true,
            'asset_url' => 'fa-solid fa-skull-crossbones',
            'atribut' => [
                'tagline' => 'Risiko Tinggi, Hadiah Lebih Besar',
                'tipe_efek' => 'multiplier',
                'stok_awal' => 2,
                'detail_efek' => 'Benar: +250% Poin | Salah: -50% Poin (Penalti Pengurangan).',
                'icon' => 'fa-solid fa-skull-crossbones',
                'tema_warna' => 'rose',
            ],
            'is_active' => true,
        ],
        [
            'id' => 2,
            'kode' => 'second_chance',
            'nama' => 'Second Chance',
            'kategori' => 'power',
            'deskripsi' => 'Memberi kesempatan kedua untuk menjawab jika jawaban pertamamu salah, tanpa kehilangan poin penuh.',
            'rarity' => 'epic',
            'bobot_gacha' => 15,
            'is_stackable' => true,
            'asset_url' => 'fa-solid fa-rotate-left',
            'atribut' => [
                'tagline' => 'Kesempatan Kedua',
                'tipe_efek' => 'second_chance',
                'stok_awal' => 2,
                'detail_efek' => 'Jika pilihan pertama salah, kamu bisa memilih jawaban sekali lagi.',
                'icon' => 'fa-solid fa-rotate-left',
                'tema_warna' => 'emerald',
            ],
            'is_active' => true,
        ],
        [
            'id' => 3,
            'kode' => 'lucky_clover',
            'nama' => 'Lucky Clover',
            'kategori' => 'power',
            'deskripsi' => 'Membuka petunjuk atau kata kunci penting dari materi yang berkaitan dengan pertanyaan soal ini.',
            'rarity' => 'common',
            'bobot_gacha' => 25,
            'is_stackable' => true,
            'asset_url' => 'fa-solid fa-clover',
            'atribut' => [
                'tagline' => 'Dapatkan Petunjuk Soal',
                'tipe_efek' => 'hint',
                'stok_awal' => 2,
                'detail_efek' => 'Menampilkan bocoran materi atau kata kunci pembantu jawaban.',
                'icon' => 'fa-solid fa-clover',
                'tema_warna' => 'teal',
            ],
            'is_active' => true,
        ],
        [
            'id' => 4,
            'kode' => 'two_x_score',
            'nama' => '2X Score',
            'kategori' => 'power',
            'deskripsi' => 'Melipatgandakan poin yang diperoleh menjadi 2 kali lipat jika jawabanmu pada soal ini benar.',
            'rarity' => 'epic',
            'bobot_gacha' => 15,
            'is_stackable' => true,
            'asset_url' => 'fa-solid fa-bolt',
            'atribut' => [
                'tagline' => 'Lipat Gandakan Poinmu',
                'tipe_efek' => 'multiplier',
                'stok_awal' => 2,
                'detail_efek' => 'Benar: +200% Poin ganda langsung.',
                'icon' => 'fa-solid fa-bolt',
                'tema_warna' => 'amber',
            ],
            'is_active' => true,
        ],
        [
            'id' => 5,
            'kode' => 'remove_wrong_answer',
            'nama' => '50 : 50',
            'kategori' => 'power',
            'deskripsi' => 'Mengeliminasi dan mencoret hingga 2 pilihan jawaban yang salah, memperbesar peluangmu memilih jawaban benar.',
            'rarity' => 'common',
            'bobot_gacha' => 25,
            'is_stackable' => true,
            'asset_url' => 'fa-solid fa-filter-circle-xmark',
            'atribut' => [
                'tagline' => 'Hilangkan Opsi Jawaban Salah',
                'tipe_efek' => 'eliminate_options',
                'stok_awal' => 2,
                'detail_efek' => 'Mencoret 1-2 opsi salah secara otomatis.',
                'icon' => 'fa-solid fa-filter-circle-xmark',
                'tema_warna' => 'indigo',
            ],
            'is_active' => true,
        ],

        // === BANNERS (Kategori: 'banner', stackable: false) ===
        [
            'id' => 6,
            'kode' => 'default',
            'nama' => 'Buku & Edukasi (Default)',
            'kategori' => 'banner',
            'deskripsi' => 'Tema klasik bernuansa biru dengan ilustrasi buku dan perlengkapan belajar.',
            'rarity' => 'common',
            'bobot_gacha' => 0,
            'is_stackable' => false,
            'asset_url' => null,
            'atribut' => [
                'theme' => 'light',
                'badge' => 'Default',
            ],
            'is_active' => true,
        ],
        [
            'id' => 7,
            'kode' => 'space_hero',
            'nama' => 'Space Hero Galaxy',
            'kategori' => 'banner',
            'deskripsi' => 'Petualangan kosmik pahlawan luar angkasa mengarungi bintang dan galaksi.',
            'rarity' => 'epic',
            'bobot_gacha' => 5,
            'is_stackable' => false,
            'asset_url' => 'images/Banner/space-hero.gif',
            'atribut' => [
                'theme' => 'dark',
                'badge' => 'Epic Space',
            ],
            'is_active' => true,
        ],
        [
            'id' => 8,
            'kode' => 'cyber_matrix',
            'nama' => 'Cyberpunk Synthwave',
            'kategori' => 'banner',
            'deskripsi' => 'Perspektif grid 3D neon retro 80s dengan matahari terbenam synthwave dan bintang jatuh.',
            'rarity' => 'rare',
            'bobot_gacha' => 8,
            'is_stackable' => false,
            'asset_url' => 'images/Banner/cyber-matrix.gif',
            'atribut' => [
                'theme' => 'dark',
                'badge' => 'Rare Cyber',
            ],
            'is_active' => true,
        ],
        [
            'id' => 9,
            'kode' => 'pixel_aurora',
            'nama' => 'Pixel Aurora Borealis',
            'kategori' => 'banner',
            'deskripsi' => 'Cahaya utara mistis zamrud-sian berkilau di atas hutan pinus dan rembulan malam.',
            'rarity' => 'epic',
            'bobot_gacha' => 5,
            'is_stackable' => false,
            'asset_url' => 'images/Banner/pixel-aurora.gif',
            'atribut' => [
                'theme' => 'dark',
                'badge' => 'Epic Aurora',
            ],
            'is_active' => true,
        ],
        [
            'id' => 10,
            'kode' => 'solar_flare',
            'nama' => 'Supernova Golden Core',
            'kategori' => 'banner',
            'deskripsi' => 'Ledakan energi bintang kosmik emas dengan cincin akresi partikel berputar.',
            'rarity' => 'legendary',
            'bobot_gacha' => 2,
            'is_stackable' => false,
            'asset_url' => 'images/Banner/solar-flare.gif',
            'atribut' => [
                'theme' => 'dark',
                'badge' => 'Legendary Gold',
            ],
            'is_active' => true,
        ],
        [
            'id' => 11,
            'kode' => 'deep_ocean',
            'nama' => 'Bioluminescent Abyss',
            'kategori' => 'banner',
            'deskripsi' => 'Keindahan palung laut dalam dengan ubur-ubur bercahaya neon dan gelembung berkilau.',
            'rarity' => 'rare',
            'bobot_gacha' => 8,
            'is_stackable' => false,
            'asset_url' => 'images/Banner/deep-ocean.gif',
            'atribut' => [
                'theme' => 'dark',
                'badge' => 'Rare Ocean',
            ],
            'is_active' => true,
        ],
        [
            'id' => 12,
            'kode' => 'sakura_twilight',
            'nama' => 'Sakura Twilight Moon',
            'kategori' => 'banner',
            'deskripsi' => 'Senja pastel lofi dengan guguran kelopak bunga sakura tertiup angin di depan Gunung Fuji.',
            'rarity' => 'epic',
            'bobot_gacha' => 5,
            'is_stackable' => false,
            'asset_url' => 'images/Banner/sakura-twilight.gif',
            'atribut' => [
                'theme' => 'dark',
                'badge' => 'Epic Sakura',
            ],
            'is_active' => true,
        ],
        [
            'id' => 13,
            'kode' => 'cyber_city',
            'nama' => 'Cyber City Metropolis',
            'kategori' => 'banner',
            'deskripsi' => 'Pemandangan megacity futuristik berlumur cahaya neon sci-fi dengan gedung pencakar langit berteknologi tinggi.',
            'rarity' => 'rare',
            'bobot_gacha' => 8,
            'is_stackable' => false,
            'asset_url' => 'images/Banner/cyber-city.gif',
            'atribut' => [
                'theme' => 'dark',
                'badge' => 'Rare Cyber',
            ],
            'is_active' => true,
        ],
        [
            'id' => 14,
            'kode' => 'enchanted_forest',
            'nama' => 'Enchanted Forest Realm',
            'kategori' => 'banner',
            'deskripsi' => 'Hutan magis kuno yang memesona dengan jamur bersinar, kunang-kunang peri, dan kabut hijau mistis.',
            'rarity' => 'epic',
            'bobot_gacha' => 5,
            'is_stackable' => false,
            'asset_url' => 'images/Banner/enchanted-forest.gif',
            'atribut' => [
                'theme' => 'dark',
                'badge' => 'Epic Fantasy',
            ],
            'is_active' => true,
        ],
        [
            'id' => 15,
            'kode' => 'sakura_dusk',
            'nama' => 'Sakura Dusk Petals',
            'kategori' => 'banner',
            'deskripsi' => 'Pemandangan lembayung senja dengan hembusan angin membawa guguran kelopak bunga sakura merah muda.',
            'rarity' => 'rare',
            'bobot_gacha' => 8,
            'is_stackable' => false,
            'asset_url' => 'images/Banner/sakura-dusk.gif',
            'atribut' => [
                'theme' => 'dark',
                'badge' => 'Rare Sakura',
            ],
            'is_active' => true,
        ],
        [
            'id' => 16,
            'kode' => 'snowy_village',
            'nama' => 'Cozy Snowy Village',
            'kategori' => 'banner',
            'deskripsi' => 'Desa musim dingin damai yang diselimuti salju lembut dengan cahaya lentera hangat di malam berbintang.',
            'rarity' => 'epic',
            'bobot_gacha' => 5,
            'is_stackable' => false,
            'asset_url' => 'images/Banner/snowy-village.gif',
            'atribut' => [
                'theme' => 'dark',
                'badge' => 'Epic Winter',
            ],
            'is_active' => true,
        ],
        [
            'id' => 17,
            'kode' => 'space_nebula',
            'nama' => 'Cosmic Nebula Void',
            'kategori' => 'banner',
            'deskripsi' => 'Awan kosmik nebula ultraviolet raksasa berputar memancarkan debu antariksa dan gugusan bintang terang.',
            'rarity' => 'legendary',
            'bobot_gacha' => 2,
            'is_stackable' => false,
            'asset_url' => 'images/Banner/space-nebula.gif',
            'atribut' => [
                'theme' => 'dark',
                'badge' => 'Legendary Nebula',
            ],
            'is_active' => true,
        ],
    ];

    /**
     * Scope: Item kategori Power
     */
    public function scopePowers($query)
    {
        return $query->where('kategori', 'power');
    }

    /**
     * Scope: Item kategori Banner
     */
    public function scopeBanners($query)
    {
        return $query->where('kategori', 'banner');
    }

    /**
     * Scope: Item yang sedang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'item_id');
    }

    public function menjawabs()
    {
        return $this->hasMany(Menjawab::class, 'power_id');
    }

    // === ACCESSOR COMPATIBILITY (Untuk Kemudahan UI & Sistem Kuis) ===

    public function getTaglineAttribute(): string
    {
        return $this->atribut['tagline'] ?? '';
    }

    public function getTipeEfekAttribute(): ?string
    {
        return $this->atribut['tipe_efek'] ?? null;
    }

    public function getStokAwalAttribute(): int
    {
        return (int) ($this->atribut['stok_awal'] ?? 2);
    }

    public function getDetailEfekAttribute(): string
    {
        return $this->atribut['detail_efek'] ?? ($this->deskripsi ?: '');
    }

    public function getIconAttribute(): string
    {
        return $this->atribut['icon'] ?? ($this->asset_url ?: 'fa-solid fa-cube');
    }

    public function getTemaWarnaAttribute(): string
    {
        return $this->atribut['tema_warna'] ?? 'blue';
    }

    public function getThemeAttribute(): string
    {
        return $this->atribut['theme'] ?? 'dark';
    }

    public function getBadgeAttribute(): string
    {
        return $this->atribut['badge'] ?? ucfirst($this->rarity);
    }

    public function getGifUrlAttribute(): ?string
    {
        return $this->asset_url;
    }

    public function getUiClassesAttribute(): array
    {
        return match ($this->tema_warna) {
            'rose' => [
                'icon_bg' => 'bg-rose-50 text-rose-600 border border-rose-200/80',
                'badge' => 'bg-rose-100 text-rose-700',
                'active_pill' => 'border-rose-500 bg-rose-50/40 text-rose-700 ring-rose-200',
            ],
            'emerald' => [
                'icon_bg' => 'bg-emerald-50 text-emerald-600 border border-emerald-200/80',
                'badge' => 'bg-emerald-100 text-emerald-700',
                'active_pill' => 'border-emerald-500 bg-emerald-50/40 text-emerald-700 ring-emerald-200',
            ],
            'teal' => [
                'icon_bg' => 'bg-teal-50 text-teal-600 border border-teal-200/80',
                'badge' => 'bg-teal-100 text-teal-700',
                'active_pill' => 'border-teal-500 bg-teal-50/40 text-teal-700 ring-teal-200',
            ],
            'amber' => [
                'icon_bg' => 'bg-amber-50 text-amber-600 border border-amber-200/80',
                'badge' => 'bg-amber-100 text-amber-700',
                'active_pill' => 'border-amber-500 bg-amber-50/40 text-amber-700 ring-amber-200',
            ],
            'indigo' => [
                'icon_bg' => 'bg-indigo-50 text-indigo-600 border border-indigo-200/80',
                'badge' => 'bg-indigo-100 text-indigo-700',
                'active_pill' => 'border-indigo-500 bg-indigo-50/40 text-indigo-700 ring-indigo-200',
            ],
            default => [
                'icon_bg' => 'bg-blue-50 text-blue-600 border border-blue-200/80',
                'badge' => 'bg-blue-100 text-blue-700',
                'active_pill' => 'border-blue-500 bg-blue-50/40 text-blue-700 ring-blue-200',
            ],
        };
    }

    /**
     * Memastikan katalog master items (Power & Banner) selalu tersedia di database.
     */
    public static function ensureDefaultCatalog(): void
    {
        foreach (self::DEFAULT_ITEMS as $item) {
            static::updateOrCreate(
                ['kode' => $item['kode']],
                $item
            );
        }
    }
}

