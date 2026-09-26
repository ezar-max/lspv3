<?php

namespace App\Http\Controllers;

use App\Models\SchemeMasterInstrument;
use App\Models\MasterQuestionBank;
use App\Models\MasterProductSpecification;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Models\KriteriaUnjukKerja;
use App\Models\LogAktivitas;
use App\Models\Pengguna;
use App\Models\Mapa01;
use App\Models\Mapa02;
use App\Models\PendaftaranAsesi;
use App\Models\MasterAk01;
use App\Models\MasterAk07;
use App\Models\JadwalAsesmen;
use App\Http\Requests\StoreMasterInstrumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterInstrumentController extends Controller
{
    /**
     * Pusat Pembuatan Formulir (FR.MAPA & FR.IA) Berbasis Skema untuk Admin & Asesor
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAsesor = $user && $user->peran === 'asesor';

        if ($isAsesor) {
            $asesorId = $user->id;
            $primarySkemaId = $user->skema_id;
            $jadwalSkemaIds = JadwalAsesmen::where('asesor_id', $asesorId)
                ->pluck('skema_id')
                ->unique()
                ->toArray();

            $accessibleSkemaIds = array_unique(array_filter(array_merge([$primarySkemaId], $jadwalSkemaIds)));

            if (!empty($accessibleSkemaIds)) {
                $skemaList = SkemaSertifikasi::whereIn('id', $accessibleSkemaIds)
                    ->where('status_aktif', true)
                    ->with([
                        'unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
                        'masterInstruments.questionBanks',
                        'masterInstruments.productSpecifications'
                    ])
                    ->orderBy('nama_skema', 'asc')
                    ->get();

                if ($skemaList->isEmpty()) {
                    $skemaList = SkemaSertifikasi::where('status_aktif', true)
                        ->with([
                            'unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
                            'masterInstruments.questionBanks',
                            'masterInstruments.productSpecifications'
                        ])
                        ->orderBy('nama_skema', 'asc')
                        ->get();
                }
            } else {
                $skemaList = SkemaSertifikasi::where('status_aktif', true)
                    ->with([
                        'unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
                        'masterInstruments.questionBanks',
                        'masterInstruments.productSpecifications'
                    ])
                    ->orderBy('nama_skema', 'asc')
                    ->get();
            }

            if ($skemaList->isEmpty()) {
                $selectedSkema = null;
                $selectedSkemaId = 0;
            } else {
                $requestedSkemaId = (int) $request->get('skema_id');
                if ($requestedSkemaId && $skemaList->contains('id', $requestedSkemaId)) {
                    $selectedSkemaId = $requestedSkemaId;
                } elseif ($primarySkemaId && $skemaList->contains('id', (int) $primarySkemaId)) {
                    $selectedSkemaId = (int) $primarySkemaId;
                } else {
                    $selectedSkemaId = (int) ($skemaList->first()?->id ?? 0);
                }
                $selectedSkema = $skemaList->firstWhere('id', $selectedSkemaId) ?: $skemaList->first();
                $selectedSkemaId = $selectedSkema?->id ?? 0;
            }
        } else {
            // Ambil semua skema aktif dengan unit, elemen, KUK, dan master instrument (Admin / Superadmin)
            $skemaList = SkemaSertifikasi::where('status_aktif', true)
                ->with([
                    'unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
                    'masterInstruments.questionBanks',
                    'masterInstruments.productSpecifications'
                ])
                ->orderBy('nama_skema', 'asc')
                ->get();

            if ($skemaList->isEmpty()) {
                $selectedSkema = null;
                $selectedSkemaId = 0;
            } else {
                $selectedSkemaId = (int) ($request->get('skema_id') ?: ($skemaList->first()?->id ?? 0));
                $selectedSkema = $skemaList->firstWhere('id', $selectedSkemaId) ?: $skemaList->first();
                $selectedSkemaId = $selectedSkema?->id ?? 0;
            }
        }

        // Rekan asesor pada skema yang dipilih
        $rekanAsesor = collect();
        $mapa01Master = null;
        $mapa02Master = null;
        $masterAk01 = null;
        $masterAk07 = null;
        $samplePendaftaran = null;

        if ($selectedSkema) {
            $rekanAsesor = Pengguna::where('peran', 'asesor')
                ->where(function ($q) use ($selectedSkema) {
                    $q->where('skema_id', $selectedSkema->id)
                      ->orWhereHas('jadwalAsesor', fn ($j) => $j->where('skema_id', $selectedSkema->id));
                })
                ->get();

            $mapa01Master = Mapa01::where('skema_id', $selectedSkema->id)->latest()->first();
            $mapa02Master = Mapa02::where('skema_id', $selectedSkema->id)->latest()->first();
            $masterAk01 = MasterAk01::where('skema_id', $selectedSkema->id)->first();
            $masterAk07 = MasterAk07::where('skema_id', $selectedSkema->id)->first();
            $samplePendaftaran = PendaftaranAsesi::where('skema_id', $selectedSkema->id)->latest()->first();
        }

        return view('asesor.mapa-index', compact(
            'skemaList',
            'selectedSkema',
            'selectedSkemaId',
            'rekanAsesor',
            'mapa01Master',
            'mapa02Master',
            'masterAk01',
            'masterAk07',
            'samplePendaftaran'
        ));
    }

    /**
     * Tambah/inisiator instrumen master otomatis langsung menuju halaman kelola
     */
    public function create(Request $request)
    {
        $skemaId = (int) $request->get('skema_id');
        $code = $request->get('code', 'ia05');

        // Skema WAJIB sudah ada — tidak boleh auto-create
        if (!$skemaId) {
            return redirect()->route('admin.master-muk.index')
                ->with('error', 'Silakan pilih skema sertifikasi terlebih dahulu sebelum menambah formulir.');
        }

        $skema = SkemaSertifikasi::with('unitKompetensi')->find($skemaId);
        if (!$skema) {
            return redirect()->route('admin.master-muk.index')
                ->with('error', 'Skema sertifikasi tidak ditemukan. Pastikan skema sudah dibuat dan aktif.');
        }

        $user = auth()->user();
        if ($user && $user->peran === 'asesor') {
            $accessibleSkemaIds = array_unique(array_filter(array_merge(
                [$user->skema_id],
                JadwalAsesmen::where('asesor_id', $user->id)->pluck('skema_id')->unique()->toArray()
            )));
            $accessibleInts = array_map('intval', $accessibleSkemaIds);
            if (!empty($accessibleInts) && !in_array((int) $skemaId, $accessibleInts, true)) {
                return redirect()->route('admin.master-muk.index')
                    ->with('error', 'Akses Ditolak: Anda tidak memiliki penugasan untuk skema ini.');
            }
        }

        $normalized = SchemeMasterInstrument::normalizeCode($code);
        $bnspInfo = SchemeMasterInstrument::BNSP_INSTRUMENT_MAP[$normalized] ?? null;
        $defaultTitle = $bnspInfo['full_name'] ?? ('Master Instrumen ' . strtoupper($code));
        $defaultUnitId = $skema->unitKompetensi?->first()?->id;

        // Cek apakah instrumen sudah ada — jika sudah, langsung redirect ke halaman kelola
        $existing = SchemeMasterInstrument::where('skema_id', $skemaId)
            ->where('instrument_code', $normalized)
            ->first();

        if ($existing) {
            return redirect()->route('admin.master-muk.manage', $existing->id);
        }

        // Buat instrumen baru hanya saat user secara eksplisit klik "Tambah Form +"
        $instrument = SchemeMasterInstrument::create([
            'skema_id' => $skemaId,
            'instrument_code' => $normalized,
            'unit_kompetensi_id' => $defaultUnitId,
            'title' => $defaultTitle,
            'is_active' => true,
        ]);

        LogAktivitas::create([
            'pengguna_id' => auth()->id(),
            'aktivitas' => 'Membuat Form Master Instrumen ' . strtoupper($instrument->instrument_code) . ' - ' . $instrument->title,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.master-muk.manage', $instrument->id);
    }

    /**
     * Simpan instrumen master baru
     */
    public function store(StoreMasterInstrumentRequest $request)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->has('additional_metadata')) {
            $validated['additional_metadata'] = $request->input('additional_metadata');
        }

        $instrument = SchemeMasterInstrument::create($validated);

        LogAktivitas::create([
            'pengguna_id' => auth()->id(),
            'aktivitas' => 'Membuat Master Instrumen ' . strtoupper($instrument->instrument_code) . ' - ' . $instrument->title,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.master-muk.manage', $instrument->id)
            ->with('sukses', 'Master instrumen berhasil dibuat! Silakan kelola butir soal dan parameter template.');
    }

    /**
     * Form edit data master instrumen
     */
    public function edit($id)
    {
        $instrument = SchemeMasterInstrument::with(['skema.unitKompetensi'])->findOrFail($id);
        $skemaList = SkemaSertifikasi::with('unitKompetensi')->orderBy('nama_skema', 'asc')->get();

        return view('admin.master-muk.edit', compact('instrument', 'skemaList'));
    }

    /**
     * Update data master instrumen
     */
    public function update(StoreMasterInstrumentRequest $request, $id)
    {
        $instrument = SchemeMasterInstrument::findOrFail($id);
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->has('additional_metadata')) {
            $validated['additional_metadata'] = $request->input('additional_metadata');
        }

        $instrument->update($validated);

        LogAktivitas::create([
            'pengguna_id' => auth()->id(),
            'aktivitas' => 'Memperbarui Master Instrumen ID #' . $instrument->id . ' (' . strtoupper($instrument->instrument_code) . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.master-muk.manage', $instrument->id)
            ->with('sukses', 'Pengaturan instrumen berhasil diperbarui.');
    }

    /**
     * Halaman terpadu pengelolaan butir soal & template spesifik per instrumen
     */
    public function manage($id)
    {
        $instrument = SchemeMasterInstrument::with([
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'questionBanks.kriteriaUnjukKerja.elemenKompetensi.unitKompetensi',
            'productSpecifications'
        ])->findOrFail($id);

        $user = auth()->user();
        if ($user && $user->peran === 'asesor') {
            $accessibleSkemaIds = array_unique(array_filter(array_merge(
                [$user->skema_id],
                JadwalAsesmen::where('asesor_id', $user->id)->pluck('skema_id')->unique()->toArray()
            )));
            $accessibleInts = array_map('intval', $accessibleSkemaIds);
            if (!empty($accessibleInts) && !in_array((int) $instrument->skema_id, $accessibleInts, true)) {
                return redirect()->route('admin.master-muk.index')
                    ->with('error', 'Akses Ditolak: Anda tidak memiliki penugasan untuk skema formulir ini.');
            }
        }

        $skemaList = SkemaSertifikasi::orderBy('nama_skema', 'asc')->get();

        // Kumpulkan daftar KUK untuk seluruh unit kompetensi pada skema
        $kukList = [];
        $unitsToUse = ($instrument->skema && $instrument->skema->unitKompetensi->isNotEmpty())
            ? $instrument->skema->unitKompetensi
            : ($instrument->unitKompetensi ? collect([$instrument->unitKompetensi]) : collect());

        foreach ($unitsToUse as $unit) {
            foreach ($unit->elemenKompetensi as $elemen) {
                foreach ($elemen->kriteriaUnjukKerja as $kuk) {
                    $kukList[] = [
                        'id' => $kuk->id,
                        'label' => "[{$unit->kode_unit}] {$elemen->nomor_elemen}.{$kuk->nomor_kuk} - " . \Illuminate\Support\Str::limit($kuk->pernyataan_kuk, 80)
                    ];
                }
            }
        }

        return view('admin.master-muk.manage', compact('instrument', 'kukList', 'skemaList'));
    }

    /**
     * Update metadata khusus (Petunjuk Praktik IA.02, TOR Proyek IA.04A, Alat/Bahan TUK)
     */
    public function updateMetadata(Request $request, $id)
    {
        $instrument = SchemeMasterInstrument::findOrFail($id);

        $request->validate([
            'instructions' => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'additional_metadata' => 'nullable|array',
        ]);

        $instrument->instructions = $request->input('instructions', $instrument->instructions);
        $instrument->time_limit_minutes = $request->input('time_limit_minutes', $instrument->time_limit_minutes);
        
        $meta = $instrument->additional_metadata ?? [];
        if ($request->has('metadata_scenario')) {
            $meta['scenario'] = $request->input('metadata_scenario');
        }
        if ($request->has('metadata_tools')) {
            $meta['tools_equipment'] = $request->input('metadata_tools');
        }
        if ($request->has('metadata_deliverables')) {
            $meta['deliverables'] = $request->input('metadata_deliverables');
        }
        if ($request->has('metadata_instructions')) {
            $meta['instructions'] = $request->input('metadata_instructions');
        }
        if ($request->has('metadata_default_standard')) {
            $meta['default_standard'] = $request->input('metadata_default_standard');
        }
        if ($request->has('metadata_benchmark')) {
            $meta['benchmark'] = $request->input('metadata_benchmark');
        }
        if ($request->has('metadata_kuk_standards')) {
            $meta['kuk_standards'] = $request->input('metadata_kuk_standards');
        }
        if ($request->has('metadata_standar_elemen')) {
            $meta['standar_elemen'] = $request->input('metadata_standar_elemen');
        }
        if ($request->has('metadata_kelompok_split')) {
            $meta['kelompok_split'] = (int) $request->input('metadata_kelompok_split');
        }
        if ($request->has('metadata_umpan_balik')) {
            $meta['umpan_balik'] = $request->input('metadata_umpan_balik');
        }
        if ($request->has('metadata_penyusun_validator')) {
            $meta['penyusun_validator'] = $request->input('metadata_penyusun_validator');
        }
        if ($request->has('metadata_kelompok_skenario')) {
            $meta['kelompok_skenario'] = $request->input('metadata_kelompok_skenario');
        }
        if ($request->has('metadata_kelompok_soal')) {
            $meta['kelompok_soal'] = $request->input('metadata_kelompok_soal');
        }
        $instrument->additional_metadata = $meta;
        $instrument->save();

        return redirect()->back()->with('sukses', 'Formulir ' . strtoupper($instrument->instrument_code) . ' berhasil disimpan.');
    }

    /**
     * Kloning / Duplikasi Paket Instrumen beserta seluruh bank soalnya ke Skema Baru
     */
    public function cloneInstrument(Request $request, $id)
    {
        $source = SchemeMasterInstrument::with(['questionBanks', 'productSpecifications'])->findOrFail($id);

        $request->validate([
            'target_skema_id' => 'required|exists:skema_sertifikasi,id',
            'new_title' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $newInstrument = $source->replicate();
            $newInstrument->skema_id = $request->input('target_skema_id');
            $newInstrument->title = $request->input('new_title');
            $newInstrument->created_at = now();
            $newInstrument->updated_at = now();
            $newInstrument->save();

            // Replicate question banks
            foreach ($source->questionBanks as $q) {
                $newQ = $q->replicate();
                $newQ->scheme_master_instrument_id = $newInstrument->id;
                $newQ->created_at = now();
                $newQ->updated_at = now();
                $newQ->save();
            }

            // Replicate product specifications (IA.11)
            foreach ($source->productSpecifications as $spec) {
                $newSpec = $spec->replicate();
                $newSpec->scheme_master_instrument_id = $newInstrument->id;
                $newSpec->created_at = now();
                $newSpec->updated_at = now();
                $newSpec->save();
            }

            LogAktivitas::create([
                'pengguna_id' => auth()->id(),
                'aktivitas' => "Mengkloning Master Instrumen #{$source->id} menjadi #{$newInstrument->id} ({$newInstrument->title})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('admin.master-muk.manage', $newInstrument->id)
                ->with('sukses', "Berhasil menggandakan paket instrumen ke instrumen baru (#{$newInstrument->id}).");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menggandakan instrumen: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Master Instrumen
     */
    public function destroy($id)
    {
        $instrument = SchemeMasterInstrument::findOrFail($id);
        $title = $instrument->title;
        $instrument->delete();

        LogAktivitas::create([
            'pengguna_id' => auth()->id(),
            'aktivitas' => "Menghapus Master Instrumen: {$title}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.master-muk.index')
            ->with('sukses', "Master instrumen '{$title}' berhasil dihapus.");
    }
}
