<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Siswa;
use App\Services\GachaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GachaBannerController extends Controller
{
    protected GachaService $gachaService;

    public function __construct(GachaService $gachaService)
    {
        $this->gachaService = $gachaService;
    }

    /**
     * API: Info status gacha & katalog banner untuk Mobile.
     */
    public function status(Request $request)
    {
        $siswa = $request->user()?->siswa;
        if (!$siswa) {
            return $this->notFoundResponse('Profil siswa tidak ditemukan.');
        }

        return $this->successResponse([
            'koin'             => (int) ($siswa->koin ?? 0),
            'pity_counter'     => (int) ($siswa->pity_gacha ?? 0),
            'pity_max'         => GachaService::PITY_LIMIT,
            'harga_1x'         => GachaService::HARGA_1X,
            'harga_10x'        => GachaService::HARGA_10X,
            'equipped_banner'  => $siswa->equipped_banner ?? 'default',
            'banners_list'     => $siswa->banners_list,
        ]);
    }

    /**
     * API: Tarik Gacha 1x atau 10x untuk Mobile.
     */
    public function pull(Request $request)
    {
        $siswa = $request->user()?->siswa;
        if (!$siswa) {
            return $this->notFoundResponse('Profil siswa tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'rolls' => ['required', 'integer', 'in:1,10'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $result = $this->gachaService->pull($siswa, (int) $request->input('rolls'));
            return $this->successResponse($result, 'Gacha berhasil!');
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * API: Ganti Banner Aktif Siswa untuk Mobile.
     */
    public function equipBanner(Request $request)
    {
        $siswa = $request->user()?->siswa;
        if (!$siswa) {
            return $this->notFoundResponse('Profil siswa tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'banner_code' => ['required', 'string', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $code = $request->input('banner_code');

        $equipped = Inventory::equipBanner($siswa, $code);

        if (!$equipped) {
            return $this->errorResponse('Banner tidak dikenali atau belum kamu miliki.', 400);
        }

        $activeBanner = $siswa->fresh()->active_banner;

        return $this->successResponse([
            'equipped_banner' => $code,
            'banner_data'     => $activeBanner,
        ], 'Banner berhasil dipasang!');
    }
}
