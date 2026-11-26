<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Menampilkan daftar notifikasi pengguna dengan filter, pencarian, dan metadata date grouping.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->unauthorizedResponse('Unauthenticated.');
        }

        $query = $user->notifikasi()->latest();

        // 1. Pencarian
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                    ->orWhere('pesan', 'like', "%{$keyword}%");
            });
        }

        // 2. Kategori / Status Filter
        $filter = $request->query('filter', 'semua');
        if ($filter === 'belum_dibaca') {
            $query->where('is_read', false);
        } elseif ($filter === 'tugas') {
            $query->whereIn('tipe', Notifikasi::TIPE_TUGAS);
        } elseif ($filter === 'evaluasi') {
            $query->whereIn('tipe', Notifikasi::TIPE_EVALUASI);
        } elseif ($filter === 'materi') {
            $query->whereIn('tipe', Notifikasi::TIPE_MATERI);
        } elseif ($filter === 'pengingat') {
            $query->whereIn('tipe', Notifikasi::TIPE_PENGINGAT);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $notifikasis = $query->paginate($perPage);

        $notifikasis->getCollection()->transform(fn (Notifikasi $notif) => $this->transformNotifikasi($notif));

        // Badge Counts dalam 1 query agregat
        $counts = $this->buildCounts($user->id);

        return $this->successResponse([
            'counts'      => $counts,
            'filter'      => $filter,
            'notifikasis' => $notifikasis,
        ]);
    }

    /**
     * Preview ringkas untuk dropdown lonceng di header / mobile app.
     */
    public function preview(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->unauthorizedResponse('Unauthenticated.');
        }

        $limit = max(1, min(20, (int) $request->query('limit', 6)));
        $counts = $this->buildCounts($user->id);

        $items = $user->notifikasi()
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn (Notifikasi $notif) => $this->transformNotifikasi($notif))
            ->values();

        return $this->successResponse([
            'unread_count' => $counts['unread'],
            'counts'       => $counts,
            'items'        => $items,
        ]);
    }

    /**
     * Endpoint ringan untuk mengambil ringkasan jumlah badge notifikasi saja.
     */
    public function counts(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->unauthorizedResponse('Unauthenticated.');
        }

        return $this->successResponse($this->buildCounts($user->id));
    }

    /**
     * Tandai satu notifikasi sebagai dibaca.
     */
    public function markAsRead(Request $request, Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== $request->user()->id) {
            return $this->forbiddenResponse();
        }

        if (!$notifikasi->is_read) {
            $notifikasi->markAsRead();
        }

        return $this->successResponse(
            $this->transformNotifikasi($notifikasi->fresh()),
            'Notifikasi berhasil ditandai sebagai dibaca.'
        );
    }

    /**
     * Tandai satu notifikasi sebagai dibaca (satu arah).
     */
    public function toggleRead(Request $request, Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== $request->user()->id) {
            return $this->forbiddenResponse();
        }

        if (!$notifikasi->is_read) {
            $notifikasi->markAsRead();
        }

        return $this->successResponse([
            'id'      => $notifikasi->id,
            'is_read' => $notifikasi->is_read,
            'read_at' => $notifikasi->read_at,
        ], 'Notifikasi berhasil ditandai sebagai dibaca.');
    }

    /**
     * Tandai semua notifikasi pengguna sebagai telah dibaca.
     */
    public function markAllRead(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->notifikasi()->where('is_read', false)->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            $user->clearNotifikasiCache();
        }

        return $this->successResponse(null, 'Semua notifikasi berhasil ditandai sudah dibaca.');
    }

    /**
     * Hapus satu notifikasi.
     */
    public function destroy(Request $request, Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== $request->user()->id) {
            return $this->forbiddenResponse();
        }

        $notifikasi->delete();

        return $this->successResponse(null, 'Notifikasi berhasil dihapus.');
    }

    /**
     * Hapus hanya notifikasi yang sudah dibaca milik pengguna yang sedang login.
     */
    public function clearRead(Request $request)
    {
        $user = $request->user();
        $deleted = 0;

        if ($user) {
            $deleted = $user->notifikasi()->where('is_read', true)->delete();
            $user->clearNotifikasiCache();
        }

        return $this->successResponse(
            ['deleted_count' => $deleted],
            "{$deleted} notifikasi yang sudah dibaca berhasil dihapus."
        );
    }

    /**
     * Hapus semua notifikasi milik pengguna yang sedang login.
     */
    public function clearAll(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->notifikasi()->delete();
            $user->clearNotifikasiCache();
        }

        return $this->successResponse(null, 'Semua riwayat notifikasi berhasil dibersihkan.');
    }

    /**
     * Bangun struktur counts lengkap termasuk jumlah yang sudah dibaca.
     */
    protected function buildCounts(int $userId): array
    {
        $counts = Notifikasi::countsForUser($userId);
        $counts['read'] = max(0, $counts['total'] - $counts['unread']);

        return $counts;
    }

    /**
     * Tambahkan metadata tampilan (date_group, time_ago, kategori) untuk klien API/mobile.
     */
    protected function transformNotifikasi(Notifikasi $notif): array
    {
        $createdAt = $notif->created_at;
        $dateGroup = 'Lebih Lama';
        if ($createdAt) {
            if ($createdAt->isToday()) {
                $dateGroup = 'Hari Ini';
            } elseif ($createdAt->isYesterday()) {
                $dateGroup = 'Kemarin';
            } elseif ($createdAt->greaterThanOrEqualTo(now()->startOfWeek())) {
                $dateGroup = 'Minggu Ini';
            }
        }

        $tipe = $notif->tipe ?? 'info';
        $kategori = match (true) {
            in_array($tipe, Notifikasi::TIPE_MATERI, true)    => 'materi',
            in_array($tipe, Notifikasi::TIPE_PENGINGAT, true) => 'pengingat',
            in_array($tipe, Notifikasi::TIPE_TUGAS, true)     => 'tugas',
            in_array($tipe, Notifikasi::TIPE_EVALUASI, true)  => 'evaluasi',
            default                                            => 'info',
        };

        return array_merge($notif->toArray(), [
            'kategori'   => $kategori,
            'date_group' => $dateGroup,
            'time_ago'   => $createdAt ? $createdAt->diffForHumans() : 'Baru saja',
        ]);
    }
}
