<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Siswa;
use App\Services\GachaService;
use Illuminate\Http\Request;

class GachaBannerController extends Controller
{
    protected GachaService $gachaService;

    public function __construct(GachaService $gachaService)
    {
        $this->gachaService = $gachaService;
    }

    /**
     * Halaman Wish / Gacha Antariksa (Genshin Impact Style).
     */
    public function index()
    {
        $user = auth()->user();
        $siswa = $user?->siswa;
        abort_unless($siswa, 403, 'Akses ditolak.');

        $banners = $siswa->banners_list;
        $activeBanner = $siswa->active_banner;

        return view('siswa.gacha.index', compact('siswa', 'banners', 'activeBanner'));
    }

    /**
     * Halaman Panduan & Penjelasan Sistem Wish / Gacha Antariksa.
     */
    public function panduan()
    {
        $user = auth()->user();
        $siswa = $user?->siswa;
        abort_unless($siswa, 403, 'Akses ditolak.');

        Item::ensureDefaultCatalog();
        $powers = Item::powers()->where('is_active', true)->get();
        $banners = $siswa->banners_list;

        return view('siswa.gacha.panduan', compact('siswa', 'powers', 'banners'));
    }

    /**
     * Memproses tarikan Gacha (1x atau 10x) untuk Siswa.
     */
    public function pull(Request $request)
    {
        $siswa = auth()->user()?->siswa;
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Profil siswa tidak ditemukan.'], 403);
        }

        $validated = $request->validate([
            'rolls' => ['required', 'integer', 'in:1,10'],
        ]);

        try {
            $result = $this->gachaService->pull($siswa, (int) $validated['rolls']);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mengganti banner aktif dashboard siswa.
     */
    public function equipBanner(Request $request)
    {
        $siswa = auth()->user()?->siswa;
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Profil siswa tidak ditemukan.'], 403);
        }

        $validated = $request->validate([
            'banner_code' => ['required', 'string', 'max:50'],
        ]);

        $code = $validated['banner_code'];

        $equipped = Inventory::equipBanner($siswa, $code);

        if (!$equipped) {
            return response()->json([
                'success' => false,
                'message' => 'Banner tidak valid atau belum kamu miliki.',
            ], 400);
        }

        $activeBanner = $siswa->fresh()->active_banner;

        return response()->json([
            'success'         => true,
            'message'         => 'Banner dashboard berhasil diperbarui!',
            'equipped_banner' => $code,
            'banner_data'     => $activeBanner,
        ]);
    }
}
