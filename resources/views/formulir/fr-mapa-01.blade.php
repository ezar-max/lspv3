@include('asesor.mapa-01', ['pendaftaran' => $pendaftaran, 'mapa01' => $pendaftaran->mapa01 ?? null, 'isMasterMode' => $isMasterMode ?? ($pendaftaran->id === 0)])
