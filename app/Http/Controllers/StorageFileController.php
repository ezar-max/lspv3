<?php

namespace App\Http\Controllers;

use App\Models\BuktiApl02;
use App\Models\DokumenAsesi;
use App\Models\PendaftaranAsesi;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageFileController extends Controller
{
    /**
     * Folder publik yang dapat diakses langsung tanpa login.
     */
    protected const PUBLIC_FOLDERS = [
        'pengaturan',
        'berita',
        'skema',
        'bank-soal',
    ];

    /**
     * Tampilkan berkas storage dengan perlindungan path traversal dan otorisasi dokumen sensitif.
     */
    public function show(Request $request, string $path): BinaryFileResponse
    {
        $baseDir = realpath(storage_path('app/public'));
        if (!$baseDir) {
            abort(404);
        }

        // 1. Blokir karakter berbahaya dan null-byte injection
        if (str_contains($path, "\0") || str_contains($path, "\x00")) {
            abort(404);
        }

        $fullPath = storage_path('app/public/' . $path);
        $realPath = realpath($fullPath);

        // Berkas harus ada dan berwujud file reguler
        if (!$realPath || !file_exists($realPath) || !is_file($realPath)) {
            abort(404);
        }

        // 2. Proteksi Path Traversal: pastikan realPath berada di dalam storage/app/public
        $normalizedBase = rtrim(str_replace('\\', '/', $baseDir), '/') . '/';
        $normalizedReal = str_replace('\\', '/', $realPath);

        if (!str_starts_with($normalizedReal, $normalizedBase)) {
            abort(404);
        }

        $relativePath = substr($normalizedReal, strlen($normalizedBase));
        $segments = explode('/', $relativePath);
        $folder = strtolower($segments[0] ?? '');

        // 3. Aset publik: boleh diakses siapa saja
        if (in_array($folder, self::PUBLIC_FOLDERS, true)) {
            return response()->file($realPath);
        }

        // 4. Aset terproteksi: wajib autentikasi
        if (!auth()->check()) {
            abort(403, 'Akses ke dokumen ini memerlukan autentikasi.');
        }

        $user = auth()->user();

        // Admin & Super Admin memiliki akses penuh ke seluruh dokumen
        if (in_array($user->peran, ['admin', 'superadmin'], true)) {
            return response()->file($realPath);
        }

        $filename = basename($realPath);

        // 5. Otorisasi Dokumen Asesi (KTP, KK, Rapor, Ijazah, dll)
        if ($folder === 'dokumen_asesi' || $folder === 'dokumen-asesi') {
            $dokumen = DokumenAsesi::with('pendaftaran.jadwal')
                ->where(function ($q) use ($relativePath, $filename) {
                    $q->where('file_path', 'like', '%' . $relativePath)
                      ->orWhere('file_path', 'like', '%' . $filename);
                })->first();

            if ($dokumen && $dokumen->pendaftaran) {
                $pendaftaran = $dokumen->pendaftaran;

                if ($user->peran === 'asesi') {
                    if ((int) $pendaftaran->asesi_id === (int) $user->id) {
                        return response()->file($realPath);
                    }
                    abort(403, 'Anda tidak memiliki hak akses ke dokumen peserta lain.');
                }

                if ($user->peran === 'asesor') {
                    $isAssigned = ((int) $pendaftaran->asesor_id === (int) $user->id)
                        || ((int) ($pendaftaran->jadwal?->asesor_id) === (int) $user->id)
                        || ((int) $pendaftaran->skema_id === (int) $user->skema_id);

                    if ($isAssigned) {
                        return response()->file($realPath);
                    }
                    abort(403, 'Anda bukan asesor yang ditugaskan untuk peserta ini.');
                }
            }

            abort(403, 'Dokumen tidak ditemukan atau akses ditolak.');
        }

        // 6. Otorisasi Bukti APL.02
        if ($folder === 'bukti_apl02') {
            $bukti = BuktiApl02::with('pendaftaran.jadwal')
                ->where(function ($q) use ($relativePath, $filename) {
                    $q->where('file_path', 'like', '%' . $relativePath)
                      ->orWhere('file_path', 'like', '%' . $filename);
                })->first();

            if ($bukti && $bukti->pendaftaran) {
                $pendaftaran = $bukti->pendaftaran;

                if ($user->peran === 'asesi') {
                    if ((int) $pendaftaran->asesi_id === (int) $user->id) {
                        return response()->file($realPath);
                    }
                    abort(403, 'Anda tidak memiliki hak akses ke bukti peserta lain.');
                }

                if ($user->peran === 'asesor') {
                    $isAssigned = ((int) $pendaftaran->asesor_id === (int) $user->id)
                        || ((int) ($pendaftaran->jadwal?->asesor_id) === (int) $user->id)
                        || ((int) $pendaftaran->skema_id === (int) $user->skema_id);

                    if ($isAssigned) {
                        return response()->file($realPath);
                    }
                    abort(403, 'Anda bukan asesor yang ditugaskan untuk peserta ini.');
                }
            }

            abort(403, 'Bukti tidak ditemukan atau akses ditolak.');
        }

        // 7. Otorisasi Tanda Tangan & Signatures
        if ($folder === 'tanda_tangan' || $folder === 'signatures') {
            // Tanda tangan milik profil pengguna sendiri
            if ($user->tanda_tangan && (str_contains($user->tanda_tangan, $filename) || str_contains($user->tanda_tangan, $relativePath))) {
                return response()->file($realPath);
            }

            // Asesi: hanya tanda tangan yang terkait dengan pendaftaran dirinya
            if ($user->peran === 'asesi') {
                $isRegistered = PendaftaranAsesi::where('asesi_id', $user->id)
                    ->where(function ($q) use ($filename, $relativePath) {
                        $q->where('tanda_tangan_asesi_ak01', 'like', "%$filename%")
                          ->orWhere('tanda_tangan_asesor_ak01', 'like', "%$filename%")
                          ->orWhere('tanda_tangan_asesi', 'like', "%$filename%")
                          ->orWhere('tanda_tangan_asesor', 'like', "%$filename%");
                    })->exists();

                if ($isRegistered) {
                    return response()->file($realPath);
                }

                // Cek nama berkas yang memuat ID pendaftaran asesi
                $userPendaftaranIds = PendaftaranAsesi::where('asesi_id', $user->id)->pluck('id')->toArray();
                foreach ($userPendaftaranIds as $pid) {
                    if (str_contains($filename, "_{$pid}_") || str_contains($filename, "_{$pid}.")) {
                        return response()->file($realPath);
                    }
                }

                abort(403, 'Anda tidak memiliki hak akses ke tanda tangan ini.');
            }

            // Asesor: dapat melihat tanda tangan terkait asesmen yang ditugaskan
            if ($user->peran === 'asesor') {
                return response()->file($realPath);
            }

            abort(403, 'Akses tanda tangan ditolak.');
        }

        // 8. Berita Acara (Hanya Asesor dan Admin)
        if ($folder === 'berita_acara') {
            if ($user->peran === 'asesor') {
                return response()->file($realPath);
            }
            abort(403, 'Hanya Asesor dan Admin yang dapat mengakses Berita Acara.');
        }

        // Folder lainnya dalam storage app/public
        abort(403, 'Akses tidak diizinkan.');
    }
}

