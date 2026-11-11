<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GachaService
{
    public const HARGA_1X = 20;
    public const HARGA_10X = 180;
    public const PITY_LIMIT = 30;
    public const BANNER_CHANCE_PERCENT = 3; // 3% peluang per roll sebelum pity

    /**
     * Melakukan undian gacha (1x atau 10x roll).
     *
     * @param Siswa $siswa
     * @param int $rolls (1 atau 10)
     * @return array
     */
    public function pull(Siswa $siswa, int $rolls = 1): array
    {
        if (!in_array($rolls, [1, 10], true)) {
            throw new InvalidArgumentException('Jumlah gacha harus 1 atau 10 kali.');
        }

        $cost = ($rolls === 10) ? self::HARGA_10X : self::HARGA_1X;

        return DB::transaction(function () use ($siswa, $rolls, $cost) {
            // Lock siswa record untuk mencegah race condition saldo koin
            $lockedSiswa = Siswa::where('id', $siswa->id)->lockForUpdate()->first();

            if ($lockedSiswa->koin < $cost) {
                throw new InvalidArgumentException("Koin tidak mencukupi. Butuh {$cost} koin, saldo kamu saat ini {$lockedSiswa->koin} koin.");
            }

            // Kurangi koin
            $lockedSiswa->decrement('koin', $cost);

            // Pastikan master katalog item dan inventori awal siswa tersedia
            Item::ensureDefaultCatalog();
            Inventory::initializeDefaultInventory($lockedSiswa);

            $powers = Item::powers()->where('is_active', true)->get();
            if ($powers->isEmpty()) {
                throw new InvalidArgumentException('Katalog Power belum tersedia di sistem.');
            }

            $allBanners = Item::banners()->where('is_active', true)->get();

            $results = [];
            $unlockedBanners = is_array($lockedSiswa->unlocked_banners) ? $lockedSiswa->unlocked_banners : ['default'];
            
            // Ambil juga dari inventori banner yang sudah dimiliki
            $ownedBannerCodes = Inventory::where('siswa_id', $lockedSiswa->id)
                ->whereHas('item', fn($q) => $q->where('kategori', 'banner'))
                ->with('item')
                ->get()
                ->pluck('item.kode')
                ->filter()
                ->all();
            $unlockedBanners = array_values(array_unique(array_merge($unlockedBanners, $ownedBannerCodes, ['default'])));

            $currentPity = (int) ($lockedSiswa->pity_gacha ?? 0);

            for ($i = 1; $i <= $rolls; $i++) {
                // 1. Undi 1 Item Power (Pasti dapat di setiap roll)
                $wonPower = $this->pickWeightedItem($powers);

                // Simpan / Tambahkan stok ke tabel inventories
                $inventoryPower = Inventory::firstOrCreate(
                    [
                        'siswa_id' => $lockedSiswa->id,
                        'item_id'  => $wonPower->id,
                    ],
                    [
                        'jumlah'      => 0,
                        'is_equipped' => false,
                    ]
                );
                $inventoryPower->increment('jumlah', 1);

                // 2. Evaluasi Hadiah Jackpot: Banner GIF + Pity 30x
                $currentPity++;
                $wonBanner = null;
                $isPityTriggered = false;

                $shouldGiveBanner = false;
                if ($currentPity >= self::PITY_LIMIT) {
                    $shouldGiveBanner = true;
                    $isPityTriggered = true;
                } else {
                    $rng = random_int(1, 100);
                    if ($rng <= self::BANNER_CHANCE_PERCENT) {
                        $shouldGiveBanner = true;
                    }
                }

                if ($shouldGiveBanner) {
                    // Cari master banner yang belum dimiliki oleh siswa
                    $availableBanners = $allBanners->filter(function ($b) use ($unlockedBanners) {
                        return !in_array($b->kode, $unlockedBanners, true);
                    });

                    if ($availableBanners->isNotEmpty()) {
                        // Pilih 1 banner baru secara berbobot / acak
                        $chosenBanner = $this->pickWeightedItem($availableBanners);
                        $unlockedBanners[] = $chosenBanner->kode;

                        // Tambahkan ke tabel inventories
                        Inventory::firstOrCreate(
                            [
                                'siswa_id' => $lockedSiswa->id,
                                'item_id'  => $chosenBanner->id,
                            ],
                            [
                                'jumlah'      => 1,
                                'is_equipped' => false,
                            ]
                        );

                        $wonBanner = [
                            'id'        => $chosenBanner->id,
                            'kode'      => $chosenBanner->kode,
                            'nama'      => $chosenBanner->nama,
                            'deskripsi' => $chosenBanner->deskripsi,
                            'rarity'    => $chosenBanner->rarity,
                            'badge'     => $chosenBanner->badge,
                            'gif_url'   => $chosenBanner->gif_url,
                            'is_pity'   => $isPityTriggered,
                        ];
                        $currentPity = 0; // Reset pity counter
                    } else {
                        // Jika seluruh banner sudah dimiliki, beri kompensasi bonus Power 2X Score
                        $epicPower = $powers->firstWhere('kode', 'two_x_score') ?? $wonPower;
                        $compInv = Inventory::firstOrCreate(
                            [
                                'siswa_id' => $lockedSiswa->id,
                                'item_id'  => $epicPower->id,
                            ],
                            [
                                'jumlah'      => 0,
                                'is_equipped' => false,
                            ]
                        );
                        $compInv->increment('jumlah', 1);

                        $wonBanner = [
                            'kode'            => 'all_owned_bonus',
                            'nama'            => 'Bonus Kompensasi: 2X Score',
                            'deskripsi'       => 'Seluruh Banner GIF telah kamu miliki! Kamu mendapatkan ekstra item 2X Score.',
                            'rarity'          => 'epic',
                            'is_compensation' => true,
                            'is_pity'         => $isPityTriggered,
                        ];
                        $currentPity = 0;
                    }
                }

                $results[] = [
                    'roll_number' => $i,
                    'power' => [
                        'id'         => $wonPower->id,
                        'kode'       => $wonPower->kode,
                        'nama'       => $wonPower->nama,
                        'tagline'    => $wonPower->tagline,
                        'rarity'     => $wonPower->rarity ?? 'common',
                        'icon'       => $wonPower->icon,
                        'tema_warna' => $wonPower->tema_warna,
                        'new_qty'    => $inventoryPower->fresh()->jumlah,
                    ],
                    'banner' => $wonBanner,
                ];
            }

            // Simpan perubahan Pity dan Unlocked Banners siswa
            $lockedSiswa->pity_gacha = $currentPity;
            $lockedSiswa->unlocked_banners = array_values(array_unique($unlockedBanners));
            $lockedSiswa->save();

            return [
                'success'      => true,
                'rolls'        => $rolls,
                'cost'         => $cost,
                'sisa_koin'    => (int) $lockedSiswa->koin,
                'pity_counter' => $currentPity,
                'pity_max'     => self::PITY_LIMIT,
                'items'        => $results,
            ];
        });
    }

    /**
     * Algoritma undian berbobot (Weighted Random) untuk Item.
     */
    protected function pickWeightedItem($items): Item
    {
        $totalWeight = $items->sum('bobot_gacha') ?: 100;
        $rand = random_int(1, (int) $totalWeight);

        $current = 0;
        foreach ($items as $item) {
            $weight = (int) ($item->bobot_gacha ?: 20);
            $current += $weight;
            if ($rand <= $current) {
                return $item;
            }
        }

        return $items->first();
    }
}
