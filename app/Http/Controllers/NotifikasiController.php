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
            $query->whereIn('tipe', Notifikasi::TIPE_TUGAS);
        } elseif ($filter === 'evaluasi') {
            $query->whereIn('tipe', Notifikasi::TIPE_EVALUASI);
        } elseif ($filter === 'materi') {
            $query->whereIn('tipe', Notifikasi::TIPE_MATERI);
        } elseif ($filter === 'pengingat') {
            $query->whereIn('tipe', Notifikasi::TIPE_PENGINGAT);
        }

        $notifikasis = $query->paginate(15)->withQueryString();

        // Badge Counts dalam 1 query agregat
        $counts = Notifikasi::countsForUser($user->id);
        $totalCount     = $counts['total'];
        $unreadCount    = $counts['unread'];
        $readCount      = max(0, $totalCount - $unreadCount);
        $tugasCount     = $counts['tugas'];
        $evaluasiCount  = $counts['evaluasi'];
        $materiCount    = $counts['materi'];
        $pengingatCount = $counts['pengingat'];

        return view('notifikasi.index', compact(
            'notifikasis',
            'filter',
            'totalCount',
            'unreadCount',
            'readCount',
            'tugasCount',
            'evaluasiCount',
            'materiCount',
            'pengingatCount'
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
     * Mark a single notification as read (one-way).
     */
    public function toggleRead(Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === auth()->id(), 403);

        if (!$notifikasi->is_read) {
            $notifikasi->markAsRead();
        }

        $message = 'Notifikasi berhasil ditandai sebagai dibaca.';

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'is_read' => true, 'message' => $message]);
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
            $user->clearNotifikasiCache();
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
     * Delete only read notifications for current user.
     */
    public function clearRead(Request $request)
    {
        $user = auth()->user();
        $deleted = 0;

        if ($user) {
            $deleted = $user->notifikasi()->where('is_read', true)->delete();
            $user->clearNotifikasiCache();
        }

        return redirect()->route('notifikasi.index')
            ->with('success', $deleted > 0
                ? "{$deleted} notifikasi yang sudah dibaca berhasil dihapus."
                : 'Tidak ada notifikasi yang sudah dibaca untuk dihapus.');
    }

    /**
     * Delete all notifications for current user.
     */
    public function clearAll(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $user->notifikasi()->delete();
            $user->clearNotifikasiCache();
        }

        return redirect()->route('notifikasi.index')
            ->with('success', 'Semua riwayat notifikasi Anda telah dibersihkan.');
    }
}
