<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'siswa_id',
        'item_id',
        'jumlah',
        'is_equipped',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'jumlah'      => 'integer',
            'is_equipped' => 'boolean',
            'metadata'    => 'array',
        ];
    }

    /**
     * Relasi ke data Siswa pemilik item.
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Relasi ke master Item (Unified Game Catalog).
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Backward-compatible: Relasi ke master Power melalui Item.
     */
    public function power()
    {
        return $this->belongsTo(Item::class, 'item_id')->where('kategori', 'power');
    }

    /**
     * Accessor backward-compatibility untuk power_id.
     */
    public function getPowerIdAttribute(): ?int
    {
        return $this->attributes['item_id'] ?? null;
    }

    /**
     * Scope khusus item bertipe Power.
     */
    public function scopePowers($query)
    {
        return $query->whereHas('item', function ($q) {
            $q->where('kategori', 'power');
        });
    }

    /**
     * Scope khusus item bertipe Banner.
     */
    public function scopeBanners($query)
    {
        return $query->whereHas('item', function ($q) {
            $q->where('kategori', 'banner');
        });
    }

    /**
     * Scope khusus item yang sedang aktif terpasang (equipped).
     */
    public function scopeEquipped($query)
    {
        return $query->where('is_equipped', true);
    }

    /**
     * Inisialisasi inventori bawaan siswa (Stok awal Power & Banner default).
     */
    public static function initializeDefaultInventory(Siswa $siswa): void
    {
        Item::ensureDefaultCatalog();

        // 1. Inisialisasi Power siswa jika belum ada
        $activePowers = Item::powers()->where('is_active', true)->get();
        foreach ($activePowers as $power) {
            static::firstOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'item_id'  => $power->id,
                ],
                [
                    'jumlah'      => max(0, (int) $power->stok_awal),
                    'is_equipped' => false,
                ]
            );
        }

        // 2. Inisialisasi Banner bawaan ('default') jika belum ada
        $defaultBanner = Item::banners()->where('kode', 'default')->first();
        if ($defaultBanner) {
            static::firstOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'item_id'  => $defaultBanner->id,
                ],
                [
                    'jumlah'      => 1,
                    'is_equipped' => true,
                ]
            );
        }
    }

    /**
     * Memasang (equip) banner aktif untuk siswa.
     */
    public static function equipBanner(Siswa $siswa, string $bannerCode): bool
    {
        $targetItem = Item::banners()->where('kode', $bannerCode)->first();
        if (!$targetItem) {
            return false;
        }

        return DB::transaction(function () use ($siswa, $targetItem, $bannerCode) {
            $userBanner = static::where('siswa_id', $siswa->id)
                ->where('item_id', $targetItem->id)
                ->first();

            // Jika belum tercatat di inventori tetapi ada di unlocked_banners, buat barisnya
            $unlocked = is_array($siswa->unlocked_banners) ? $siswa->unlocked_banners : ['default'];
            if (!$userBanner && in_array($bannerCode, $unlocked, true)) {
                $userBanner = static::create([
                    'siswa_id'    => $siswa->id,
                    'item_id'     => $targetItem->id,
                    'jumlah'      => 1,
                    'is_equipped' => true,
                ]);
            }

            if (!$userBanner) {
                return false;
            }

            // Un-equip semua banner milik siswa ini
            static::where('siswa_id', $siswa->id)
                ->whereHas('item', function ($q) {
                    $q->where('kategori', 'banner');
                })
                ->update(['is_equipped' => false]);

            // Pasang banner terpilih
            $userBanner->update(['is_equipped' => true]);

            // Sinkronkan ke model Siswa
            $siswa->update(['equipped_banner' => $bannerCode]);

            return true;
        });
    }
}
