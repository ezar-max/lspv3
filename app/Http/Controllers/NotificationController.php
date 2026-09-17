<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Menampilkan Halaman Notifikasi Terpusat untuk seluruh role
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $filter = $request->query('filter', 'all');

        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(15)->withQueryString();

        $totalCount = $user->notifications()->count();
        $unreadCount = $user->unreadNotifications()->count();
        $readCount = $totalCount - $unreadCount;

        return view('notifikasi.index', compact('notifications', 'totalCount', 'unreadCount', 'readCount', 'filter'));
    }

    /**
     * Menandai satu notifikasi sebagai dibaca dan mengarahkan ke target URL secara aman
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // STRICT AUTHORIZATION: Hanya boleh mengakses notifikasi miliknya sendiri
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        $targetUrl = $notification->data['url'] ?? null;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai telah dibaca.',
                'url' => $targetUrl,
            ]);
        }

        if ($targetUrl && $targetUrl !== '#') {
            return redirect($targetUrl);
        }

        return back()->with('sukses', 'Notifikasi ditandai telah dibaca.');
    }

    /**
     * Menandai semua notifikasi milik user yang sedang login sebagai dibaca
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Seluruh notifikasi berhasil ditandai sebagai dibaca.',
            ]);
        }

        return back()->with('sukses', 'Seluruh notifikasi berhasil ditandai sebagai dibaca.');
    }

    /**
     * Menghapus notifikasi spesifik milik pengguna
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus.',
            ]);
        }

        return back()->with('sukses', 'Notifikasi berhasil dihapus.');
    }
}

