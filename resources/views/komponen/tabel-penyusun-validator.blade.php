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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Penyusun (Asesor) Card -->
        <div class="relative">
            @include('komponen.signature-display', [
                'role' => 'Penyusun (Asesor Penguji)',
                'metadata' => 'No. MET: ' . $penyusunMet . ' | ' . $penyusunNama,
                'signature' => $penyusunTtd,
                'date' => 'Disahkan pada: ' . $penyusunTgl
            ])
            
            <!-- Elemen Interaktif untuk Form -->
            <input type="hidden" name="metadata_penyusun_validator[penyusun][ttd]" class="hidden-penyusun-ttd" value="{{ $penyusunTtd ?? '' }}">
            
            <!-- Overlay transparan di atas area TTD agar bisa diklik -->
            @if(isset($isMasterMode) && $isMasterMode)
                <div class="absolute inset-0 bg-transparent cursor-pointer" style="top: 60px;" onclick="if(typeof openSignaturePadPenyusun === 'function') openSignaturePadPenyusun()"></div>
                
                @if(empty($penyusunTtd))
                    <div class="absolute bottom-6 left-1/2 -translate-x-1/2">
                        <button type="button" onclick="if(typeof openSignaturePadPenyusun === 'function') openSignaturePadPenyusun()" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold hover:bg-indigo-100 shadow-sm flex items-center gap-2 transition-colors">
                            <i class="fa-solid fa-pen"></i> Bubuhkan TTD
                        </button>
                    </div>
                @endif
                
                <!-- Gambar preview tersembunyi untuk signature pad JS -->
                <img src="" class="preview-penyusun-ttd hidden absolute bottom-10 left-1/2 -translate-x-1/2 max-h-20 object-contain z-10" style="pointer-events: none;">
            @endif
        </div>

        <!-- Validator (Admin) Card -->
        <div>
            @include('komponen.signature-display', [
                'role' => 'Validator (Admin LSP)',
                'metadata' => 'No. MET: ' . $validatorMet . ' | ' . $validatorNama,
                'signature' => $validatorTtd,
                'date' => 'Divalidasi pada: ' . $validatorTgl
            ])
        </div>
    </div>
</div>

@if(isset($isMasterMode) && $isMasterMode)
    @include('komponen.signature-pad-penyusun')
@endif
