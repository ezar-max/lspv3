@include('asesor.mapa-02', ['pendaftaran' => $pendaftaran, 'mapa02' => $pendaftaran->mapa02 ?? null, 'isMasterMode' => $isMasterMode ?? ($pendaftaran->id === 0)])
