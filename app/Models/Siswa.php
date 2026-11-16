<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswas';

    protected $fillable = [
        'nama_siswa',
        'nis',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'wali_murid',
        'nohp_wali',
        'status',
        'koin',
        'pity_gacha',
        'equipped_banner',
        'unlocked_banners',
    ];

    protected function casts(): array
    {
        return [
            'koin'             => 'integer',
            'pity_gacha'       => 'integer',
            'unlocked_banners' => 'array',
        ];
    }

    /**
     * Fallback Katalog Banner Statis (Kompatibilitas).
     */
    public const BANNER_CATALOG = [
        'default' => [
            'kode' => 'default',
            'nama' => 'Buku & Edukasi (Default)',
            'deskripsi' => 'Tema klasik bernuansa biru dengan ilustrasi buku dan perlengkapan belajar.',
            'gif_url' => null,
            'theme' => 'light',
            'rarity' => 'common',
            'badge' => 'Default',
        ],
        'space_hero' => [
            'kode' => 'space_hero',
            'nama' => 'Space Hero Galaxy',
            'deskripsi' => 'Petualangan kosmik pahlawan luar angkasa mengarungi bintang dan galaksi.',
            'gif_url' => 'images/Banner/space-hero.gif',
            'theme' => 'dark',
            'rarity' => 'epic',
            'badge' => 'Epic Space',
        ],
        'cyber_matrix' => [
            'kode' => 'cyber_matrix',
            'nama' => 'Cyberpunk Synthwave',
            'deskripsi' => 'Perspektif grid 3D neon retro 80s dengan matahari terbenam synthwave dan bintang jatuh.',
            'gif_url' => 'images/Banner/cyber-matrix.gif',
            'theme' => 'dark',
            'rarity' => 'rare',
            'badge' => 'Rare Cyber',
        ],
        'pixel_aurora' => [
            'kode' => 'pixel_aurora',
            'nama' => 'Pixel Aurora Borealis',
            'deskripsi' => 'Cahaya utara mistis zamrud-sian berkilau di atas hutan pinus dan rembulan malam.',
            'gif_url' => 'images/Banner/pixel-aurora.gif',
            'theme' => 'dark',
            'rarity' => 'epic',
            'badge' => 'Epic Aurora',
        ],
        'solar_flare' => [
            'kode' => 'solar_flare',
            'nama' => 'Supernova Golden Core',
            'deskripsi' => 'Ledakan energi bintang kosmik emas dengan cincin akresi partikel berputar.',
            'gif_url' => 'images/Banner/solar-flare.gif',
            'theme' => 'dark',
            'rarity' => 'legendary',
            'badge' => 'Legendary Gold',
        ],
        'deep_ocean' => [
            'kode' => 'deep_ocean',
            'nama' => 'Bioluminescent Abyss',
            'deskripsi' => 'Keindahan palung laut dalam dengan ubur-ubur bercahaya neon dan gelembung berkilau.',
            'gif_url' => 'images/Banner/deep-ocean.gif',
            'theme' => 'dark',
            'rarity' => 'rare',
            'badge' => 'Rare Ocean',
        ],
        'sakura_twilight' => [
            'kode' => 'sakura_twilight',
            'nama' => 'Sakura Twilight Moon',
            'deskripsi' => 'Senja pastel lofi dengan guguran kelopak bunga sakura tertiup angin di depan Gunung Fuji.',
            'gif_url' => 'images/Banner/sakura-twilight.gif',
            'theme' => 'dark',
            'rarity' => 'epic',
            'badge' => 'Epic Sakura',
        ],
        'cyber_city' => [
            'kode' => 'cyber_city',
            'nama' => 'Cyber City Metropolis',
            'deskripsi' => 'Pemandangan megacity futuristik berlumur cahaya neon sci-fi dengan gedung pencakar langit berteknologi tinggi.',
            'gif_url' => 'images/Banner/cyber-city.gif',
            'theme' => 'dark',
            'rarity' => 'rare',
            'badge' => 'Rare Cyber',
        ],
        'enchanted_forest' => [
            'kode' => 'enchanted_forest',
            'nama' => 'Enchanted Forest Realm',
            'deskripsi' => 'Hutan magis kuno yang memesona dengan jamur bersinar, kunang-kunang peri, dan kabut hijau mistis.',
            'gif_url' => 'images/Banner/enchanted-forest.gif',
            'theme' => 'dark',
            'rarity' => 'epic',
            'badge' => 'Epic Fantasy',
        ],
        'sakura_dusk' => [
            'kode' => 'sakura_dusk',
            'nama' => 'Sakura Dusk Petals',
            'deskripsi' => 'Pemandangan lembayung senja dengan hembusan angin membawa guguran kelopak bunga sakura merah muda.',
            'gif_url' => 'images/Banner/sakura-dusk.gif',
            'theme' => 'dark',
            'rarity' => 'rare',
            'badge' => 'Rare Sakura',
        ],
        'snowy_village' => [
            'kode' => 'snowy_village',
            'nama' => 'Cozy Snowy Village',
            'deskripsi' => 'Desa musim dingin damai yang diselimuti salju lembut dengan cahaya lentera hangat di malam berbintang.',
            'gif_url' => 'images/Banner/snowy-village.gif',
            'theme' => 'dark',
            'rarity' => 'epic',
            'badge' => 'Epic Winter',
        ],
        'space_nebula' => [
            'kode' => 'space_nebula',
            'nama' => 'Cosmic Nebula Void',
            'deskripsi' => 'Awan kosmik nebula ultraviolet raksasa berputar memancarkan debu antariksa dan gugusan bintang terang.',
            'gif_url' => 'images/Banner/space-nebula.gif',
            'theme' => 'dark',
            'rarity' => 'legendary',
            'badge' => 'Legendary Nebula',
        ],
    ];

    /**
     * Mengambil metadata banner yang sedang dipasang siswa.
     */
    public function getActiveBannerAttribute(): array
    {
        // 1. Coba cari dari tabel inventories item banner yang equipped
        $equippedInventory = $this->inventories()
            ->with('item')
            ->where('is_equipped', true)
            ->whereHas('item', fn($q) => $q->where('kategori', 'banner'))
            ->first();

        if ($equippedInventory && $equippedInventory->item) {
            $item = $equippedInventory->item;
            return [
                'kode'      => $item->kode,
                'nama'      => $item->nama,
                'deskripsi' => $item->deskripsi,
                'gif_url'   => $item->gif_url,
                'theme'     => $item->theme,
                'rarity'    => $item->rarity,
                'badge'     => $item->badge,
            ];
        }

        // 2. Fallback berdasarkan kolom equipped_banner
        $code = $this->equipped_banner ?? 'default';
        $item = Item::banners()->where('kode', $code)->first();
        if ($item) {
            return [
                'kode'      => $item->kode,
                'nama'      => $item->nama,
                'deskripsi' => $item->deskripsi,
                'gif_url'   => $item->gif_url,
                'theme'     => $item->theme,
                'rarity'    => $item->rarity,
                'badge'     => $item->badge,
            ];
        }

        return self::BANNER_CATALOG[$code] ?? self::BANNER_CATALOG['default'];
    }

    /**
     * Mengambil daftar koleksi banner master beserta status kepemilikan siswa.
     */
    public function getBannersListAttribute(): array
    {
        Item::ensureDefaultCatalog();
        $allBanners = Item::banners()->where('is_active', true)->orderBy('id')->get();

        // Ambil daftar kode banner yang dimiliki dari inventories
        $ownedCodes = $this->inventories()
            ->with('item')
            ->whereHas('item', fn($q) => $q->where('kategori', 'banner'))
            ->get()
            ->pluck('item.kode')
            ->filter()
            ->all();

        // Tambahkan juga dari unlocked_banners untuk fallback
        $unlockedJson = is_array($this->unlocked_banners) ? $this->unlocked_banners : ['default'];
        $allOwned = array_unique(array_merge($ownedCodes, $unlockedJson, ['default']));

        $currentEquipped = $this->equipped_banner ?? 'default';

        $result = [];
        if ($allBanners->isNotEmpty()) {
            foreach ($allBanners as $banner) {
                $isUnlocked = in_array($banner->kode, $allOwned, true);
                $isEquipped = ($currentEquipped === $banner->kode);

                $result[] = [
                    'id'          => $banner->id,
                    'kode'        => $banner->kode,
                    'nama'        => $banner->nama,
                    'deskripsi'   => $banner->deskripsi,
                    'gif_url'     => $banner->gif_url,
                    'theme'       => $banner->theme,
                    'rarity'      => $banner->rarity,
                    'badge'       => $banner->badge,
                    'is_unlocked' => $isUnlocked,
                    'is_equipped' => $isEquipped,
                ];
            }
        } else {
            // Fallback ke static catalog jika master item belum ada
            foreach (self::BANNER_CATALOG as $code => $data) {
                $isUnlocked = in_array($code, $allOwned, true);
                $isEquipped = ($currentEquipped === $code);
                $result[] = array_merge($data, [
                    'is_unlocked' => $isUnlocked,
                    'is_equipped' => $isEquipped,
                ]);
            }
        }

        return $result;
    }

    public function kelas()
    {
        return $this->hasOneThrough(
            Kelas::class,
            AnggotaKelas::class,
            'siswa_id',
            'id',
            'id',
            'kelas_id'
        )
            ->where('anggota_kelases.tahun_ajaran', '2026/2027')
            ->where('anggota_kelases.semester', 'ganjil');
    }

    public function anggotaKelases()
    {
        return $this->hasMany(AnggotaKelas::class, 'siswa_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'siswa_id');
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'siswa_id');
    }

    public function menjawabs()
    {
        return $this->hasMany(Menjawab::class, 'siswa_id');
    }

    public function seleksiUjians()
    {
        return $this->hasMany(SeleksiUjian::class, 'siswa_id');
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class, 'siswa_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'siswa_id');
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'inventories', 'siswa_id', 'item_id')
            ->withPivot(['id', 'jumlah', 'is_equipped', 'metadata'])
            ->withTimestamps();
    }

    public function siswaPowers()
    {
        return $this->hasMany(SiswaPower::class, 'siswa_id');
    }

    public function powers()
    {
        return $this->belongsToMany(Power::class, 'inventories', 'siswa_id', 'item_id')
            ->withPivot(['id', 'jumlah', 'is_equipped', 'metadata'])
            ->withTimestamps();
    }
}
