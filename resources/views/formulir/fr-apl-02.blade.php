@include('asesor.input-penilaian', [
    'pendaftaran' => $pendaftaran,
    'jawabanMap' => $pendaftaran->jawabanApl02 ? $pendaftaran->jawabanApl02->keyBy('elemen_id') : collect([]),
    'isLocked' => (!empty($pendaftaran->rekomendasi_asesor_status) || !empty($pendaftaran->rekomendasi))
])
