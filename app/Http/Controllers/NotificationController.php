<?php

namespace App\Http\Controllers;

use App\Models\Mapa01;
use App\Notifications\SystemAlert;
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

        $pendingMapa01Skema = collect();

        // Bagi Admin & Superadmin: Sinkronisasi notifikasi jika ada formulir Master FR.MAPA.01 yang perlu divalidasi
        if (in_array($user->peran, ['admin', 'superadmin'])) {
            $pendingMapa01Skema = Mapa01::with(['skema', 'asesor'])
                ->whereNull('pendaftaran_id')
                ->whereNotNull('skema_id')
                ->get()
                ->filter(function ($m) {
                    $val = $m->penyusun_validator_tabel['validator_1'] ?? [];
                    return empty($val['ttd']) && ($val['status_validasi'] ?? null) !== 'tervalidasi';
                });

            foreach ($pendingMapa01Skema as $m) {
                $skema = $m->skema;
                if (!$skema) continue;

                $hasPendingNotification = $user->notifications()
                    ->where('type', SystemAlert::class)
                    ->get()
                    ->contains(function ($notification) use ($skema) {
                        return ($notification->data['metadata']['mapa01_master_skema_id'] ?? null) == $skema->id
                            && ($notification->data['metadata']['status'] ?? null) === 'menunggu_validasi'
                            && is_null($notification->read_at);
                    });

                if (!$hasPendingNotification) {
                    $user->notify(new SystemAlert(
                        'FR.MAPA.01 Sesuai Skema Menunggu Validasi',
                        'Terdapat dokumen Master FR.MAPA.01 untuk Skema ' . ($skema->nama_skema ?: 'Sertifikasi') . ' (' . $skema->kode_skema . ') yang perlu divalidasi oleh Administrator LSP.',
                        route('asesor.skema.mapa-01', $skema->id),
                        'mapa_validation',
                        [
                            'mapa01_master_skema_id' => $skema->id,
                            'skema_id' => $skema->id,
                            'status' => 'menunggu_validasi',
                        ]
                    ));
                }
            }
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

        return view('notifikasi.index', compact('notifications', 'totalCount', 'unreadCount', 'readCount', 'filter', 'pendingMapa01Skema'));
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

