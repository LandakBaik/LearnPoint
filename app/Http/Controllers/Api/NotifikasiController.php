<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Menampilkan daftar notifikasi pengguna dengan filter dan pencarian.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
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
            $query->whereIn('tipe', ['tugas_dikumpulkan', 'tugas_terlambat', 'tugas_baru', 'nilai_tugas']);
        } elseif ($filter === 'evaluasi') {
            $query->whereIn('tipe', ['kuis_selesai', 'ujian_selesai', 'kuis_baru', 'ujian_baru']);
        }

        $perPage = (int) $request->query('per_page', 15);
        $notifikasis = $query->paginate($perPage);

        // Badge Counts
        $totalCount    = $user->notifikasi()->count();
        $unreadCount   = $user->notifikasi()->where('is_read', false)->count();
        $tugasCount    = $user->notifikasi()->whereIn('tipe', ['tugas_dikumpulkan', 'tugas_terlambat', 'tugas_baru', 'nilai_tugas'])->count();
        $evaluasiCount = $user->notifikasi()->whereIn('tipe', ['kuis_selesai', 'ujian_selesai', 'kuis_baru', 'ujian_baru'])->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'counts' => [
                    'total'        => $totalCount,
                    'unread'       => $unreadCount,
                    'tugas'        => $tugasCount,
                    'evaluasi'     => $evaluasiCount,
                ],
                'filter' => $filter,
                'notifikasis' => $notifikasis,
            ],
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai dibaca.
     */
    public function markAsRead(Request $request, Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $notifikasi->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai sebagai dibaca.',
            'data'    => $notifikasi,
        ]);
    }

    /**
     * Toggle status dibaca / belum dibaca untuk satu notifikasi.
     */
    public function toggleRead(Request $request, Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if ($notifikasi->is_read) {
            $notifikasi->markAsUnread();
            $message = 'Notifikasi ditandai sebagai belum dibaca.';
        } else {
            $notifikasi->markAsRead();
            $message = 'Notifikasi berhasil ditandai sebagai dibaca.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => [
                'id'      => $notifikasi->id,
                'is_read' => $notifikasi->is_read,
                'read_at' => $notifikasi->read_at,
            ],
        ]);
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
        }

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil ditandai sudah dibaca.',
        ]);
    }

    /**
     * Hapus satu notifikasi.
     */
    public function destroy(Request $request, Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $notifikasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dihapus.',
        ]);
    }

    /**
     * Hapus semua notifikasi milik pengguna yang sedang login.
     */
    public function clearAll(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->notifikasi()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Semua riwayat notifikasi berhasil dibersihkan.',
        ]);
    }
}
