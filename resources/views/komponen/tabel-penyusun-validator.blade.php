@php
    $tableClass = $tableClass ?? 'tabel-bnsp';
    $kodeFormNormalized = strtolower(str_replace(['.', '-', ' '], '', $kodeForm ?? ''));
    if (str_starts_with($kodeFormNormalized, 'fr')) {
        $kodeFormNormalized = substr($kodeFormNormalized, 2);
    }
    
    // Tentukan skema_id aktif
    $skemaId = null;
    if (isset($pendaftaran) && !empty($pendaftaran->skema_id)) {
        $skemaId = $pendaftaran->skema_id;
    } elseif (isset($pendaftaran) && !empty($pendaftaran->skema?->id)) {
        $skemaId = $pendaftaran->skema->id;
    } elseif (isset($skema) && !empty($skema->id)) {
        $skemaId = $skema->id;
    } elseif (isset($instrument) && !empty($instrument->skema_id)) {
        $skemaId = $instrument->skema_id;
    } elseif (request()->filled('skema_id')) {
        $skemaId = (int) request('skema_id');
    } elseif (session()->has('active_selected_skema_id')) {
        $skemaId = (int) session('active_selected_skema_id');
    }

    $skemaModel = isset($pendaftaran) && !empty($pendaftaran->skema) 
        ? $pendaftaran->skema 
        : ($skemaId ? \App\Models\SkemaSertifikasi::find($skemaId) : null);

    // 1. CARI DATA PENYUSUN (Akun yang bikin form / master instrument untuk skema tersebut)
    // - Jika form dibuat oleh akun asesor/user pembuat form, ambil akun pembuat tersebut
    // - Karena tiap skema memiliki master form sendiri, jika belum ada creator eksplisit, ambil Asesor pembuat/penyusun pada skema tersebut
    $masterInstrument = isset($instrument) ? $instrument : null;
    if (!$masterInstrument && $skemaModel) {
        if ($skemaModel->relationLoaded('masterInstruments')) {
            $masterInstrument = $skemaModel->masterInstruments->first(function ($inst) use ($kodeFormNormalized) {
                $instCode = strtolower(str_replace(['.', '-', '_'], '', $inst->instrument_code));
                return $instCode === $kodeFormNormalized;
            });
        }
        if (!$masterInstrument && !empty($skemaModel->id)) {
            $masterInstrument = \App\Models\SchemeMasterInstrument::with('creator')
                ->where('skema_id', $skemaModel->id)
                ->where(function ($q) use ($kodeFormNormalized) {
                    $q->where('instrument_code', $kodeFormNormalized)
                      ->orWhere('instrument_code', 'like', "%{$kodeFormNormalized}%");
                })
                ->first();
        }
    }

    // Resolusi akun penyusun (Bukan superadmin, tapi akun pembuat form / asesor yang menyusun skema ini):
    $penyusunUser = null;
    // a. Cek akun yang membuat form di SchemeMasterInstrument (jika pembuatnya bukan superadmin)
    if ($masterInstrument && $masterInstrument->creator && $masterInstrument->creator->peran !== 'superadmin') {
        $penyusunUser = $masterInstrument->creator;
    } elseif ($masterInstrument && !empty($masterInstrument->created_by)) {
        $foundCreator = \App\Models\Pengguna::find($masterInstrument->created_by);
        if ($foundCreator && $foundCreator->peran !== 'superadmin') {
            $penyusunUser = $foundCreator;
        }
    }

    // b. Jika form belum memiliki creator khusus atau pembuatnya superadmin,
    // ambil Asesor yang terdaftar/memegang skema tersebut (kolom skema_id di tabel pengguna)
    if (!$penyusunUser && $skemaId) {
        $penyusunUser = \App\Models\Pengguna::where('peran', 'asesor')->where('skema_id', $skemaId)->first();
        
        if (!$penyusunUser) {
            $penyusunUser = \App\Models\JadwalAsesmen::where('skema_id', $skemaId)
                ->whereNotNull('asesor_id')
                ->with('asesor')
                ->first()?->asesor;
        }
    }

    // c. Jika form dibuka dalam konteks pendaftaran asesmen dan ada asesor penguji
    if (!$penyusunUser && isset($pendaftaran) && !empty($pendaftaran->asesor)) {
        $penyusunUser = $pendaftaran->asesor;
    }

    // d. Jika pengguna yang sedang login adalah asesor (misal saat asesor sedang membuat/mengedit form)
    if (!$penyusunUser && auth()->check() && auth()->user()->peran === 'asesor') {
        $penyusunUser = auth()->user();
    }

    // e. Fallback ke asesor pertama di database jika ada
    if (!$penyusunUser) {
        $penyusunUser = \App\Models\Pengguna::where('peran', 'asesor')->first();
    }

    // f. Fallback darurat jika di database sama sekali tidak ada asesor
    if (!$penyusunUser) {
        $penyusunUser = \App\Models\Pengguna::where('peran', 'admin')->orderBy('id', 'asc')->first();
    }

    $penyusunNama = $penyusunUser?->nama_lengkap ?? 'Asesor Penyusun MUK';
    $penyusunMet = $penyusunUser?->nomor_registrasi ?? 'MET.000.001222 2026';
    $penyusunTtd = $penyusunUser?->tanda_tangan ?? null;
    $penyusunTgl = $masterInstrument?->created_at 
        ? $masterInstrument->created_at->format('d-m-Y') 
        : ($skemaModel?->created_at ? $skemaModel->created_at->format('d-m-Y') : date('d-m-Y'));

    // 2. CARI DATA VALIDATOR (Akun Admin biasa LSP)
    $adminValidator = null;
    if (auth()->check() && auth()->user()->peran === 'admin') {
        $adminValidator = auth()->user();
    }
    
    if (!$adminValidator) {
        $adminValidator = \App\Models\Pengguna::where('peran', 'admin')->orderBy('id', 'asc')->first();
    }

    if (!$adminValidator) {
        $adminValidator = \App\Models\Pengguna::where('peran', 'superadmin')->orderBy('id', 'asc')->first();
    }

    $validatorNama = $adminValidator?->nama_lengkap ?? 'Administrator LSP';
    $validatorMet = $adminValidator?->nomor_registrasi ?? 'REG.ADM.LSP.001';
    $validatorTtd = $adminValidator?->tanda_tangan ?? null;
    $validatorTgl = isset($pendaftaran) && $pendaftaran->jadwal?->tanggal_uji 
        ? \Carbon\Carbon::parse($pendaftaran->jadwal->tanggal_uji)->format('d-m-Y') 
        : date('d-m-Y');
@endphp

<div style="margin-top: 2rem;">
    <div style="font-weight: 800; font-size: 0.95rem; margin-bottom: 0.6rem; color: #0f172a; text-transform: uppercase;">
        PENYUSUN DAN VALIDATOR
    </div>
    <table class="{{ $tableClass }}" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="width: 18%; text-align: center;">STATUS</th>
                <th style="width: 6%; text-align: center;">NO</th>
                <th style="width: 32%;">NAMA</th>
                <th style="width: 22%;">NOMOR MET</th>
                <th style="width: 22%; text-align: center;">TANDA TANGAN DAN TANGGAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight: 800; vertical-align: middle; text-align: center; background-color: #f8fafc;">PENYUSUN</td>
                <td style="text-align: center; font-weight: 700;">1</td>
                <td><strong>{{ $penyusunNama }}</strong></td>
                <td>{{ $penyusunMet }}</td>
                <td style="text-align: center;">
                    @if($penyusunTtd)
                        @php
                            $penyusunTtdSrc = (str_starts_with($penyusunTtd, 'data:image') || str_starts_with($penyusunTtd, 'http://') || str_starts_with($penyusunTtd, 'https://')) ? $penyusunTtd : asset($penyusunTtd);
                        @endphp
                        <img src="{{ $penyusunTtdSrc }}" alt="TTD Penyusun" style="max-height: 38px; margin: 0 auto; display: block;">
                    @endif
                    <span style="font-size: 0.78rem; color: #64748b;">{{ $penyusunTgl }}</span>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 800; vertical-align: middle; text-align: center; background-color: #f8fafc;">VALIDATOR</td>
                <td style="text-align: center; font-weight: 700;">1</td>
                <td><strong>{{ $validatorNama }}</strong></td>
                <td>{{ $validatorMet }}</td>
                <td style="text-align: center;">
                    @if($validatorTtd)
                        @php
                            $validatorTtdSrc = (str_starts_with($validatorTtd, 'data:image') || str_starts_with($validatorTtd, 'http://') || str_starts_with($validatorTtd, 'https://')) ? $validatorTtd : asset($validatorTtd);
                        @endphp
                        <img src="{{ $validatorTtdSrc }}" alt="TTD Validator" style="max-height: 38px; margin: 0 auto; display: block;">
                    @endif
                    <span style="font-size: 0.78rem; color: #64748b;">{{ $validatorTgl }}</span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
