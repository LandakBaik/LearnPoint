<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Display notifications list with interactive filtering and search.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $query = $user->notifikasi()->latest();

        // 1. Search filter
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                  ->orWhere('pesan', 'like', "%{$keyword}%");
            });
        }

        // 2. Category / Status filter
        $filter = $request->query('filter', 'semua');
        if ($filter === 'belum_dibaca') {
            $query->where('is_read', false);
        } elseif ($filter === 'tugas') {
            $query->whereIn('tipe', ['tugas_dikumpulkan', 'tugas_terlambat', 'tugas_baru', 'nilai_tugas']);
        } elseif ($filter === 'evaluasi') {
            $query->whereIn('tipe', ['kuis_selesai', 'ujian_selesai', 'kuis_baru', 'ujian_baru']);
        }

        $notifikasis = $query->paginate(15)->withQueryString();

        // Badge Counts
        $totalCount    = $user->notifikasi()->count();
        $unreadCount   = $user->notifikasi()->where('is_read', false)->count();
        $tugasCount    = $user->notifikasi()->whereIn('tipe', ['tugas_dikumpulkan', 'tugas_terlambat', 'tugas_baru', 'nilai_tugas'])->count();
        $evaluasiCount = $user->notifikasi()->whereIn('tipe', ['kuis_selesai', 'ujian_selesai', 'kuis_baru', 'ujian_baru'])->count();

        return view('notifikasi.index', compact(
            'notifikasis',
            'filter',
            'totalCount',
            'unreadCount',
            'tugasCount',
            'evaluasiCount'
        ));
    }

    /**
     * Mark notification as read and redirect directly to its target URL.
     */
    public function readAndRedirect(Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === auth()->id(), 403);

        $notifikasi->markAsRead();

        if ($notifikasi->url) {
            return redirect($notifikasi->url);
        }

        return redirect()->route('notifikasi.index')
            ->with('success', 'Notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Toggle read / unread status for a single notification.
     */
    public function toggleRead(Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === auth()->id(), 403);

        if ($notifikasi->is_read) {
            $notifikasi->markAsUnread();
            $message = 'Notifikasi ditandai sebagai belum dibaca.';
        } else {
            $notifikasi->markAsRead();
            $message = 'Notifikasi berhasil ditandai sebagai dibaca.';
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'is_read' => $notifikasi->is_read, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function markAllRead(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $user->notifikasi()->where('is_read', false)->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Semua notifikasi berhasil ditandai sudah dibaca.']);
        }

        return back()->with('success', 'Semua notifikasi berhasil ditandai sudah dibaca.');
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === auth()->id(), 403);

        $notifikasi->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    /**
     * Delete all notifications for current user.
     */
    public function clearAll(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $user->notifikasi()->delete();
        }

        return redirect()->route('notifikasi.index')
            ->with('success', 'Semua riwayat notifikasi Anda telah dibersihkan.');
    }
}
