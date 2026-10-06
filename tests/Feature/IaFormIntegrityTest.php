<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pendaftaran;
use App\Models\SchemeMasterInstrument;
use App\Models\IaPenilaian;

class IaFormIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test Master IA10 Data Leak Prevention.
     * Pastikan master tidak menyimpan jawaban wawancara dan data pihak ketiga
     */
    public function test_master_mode_ia10_does_not_save_execution_data(): void
    {
        // 1. Setup Data Asesor (Super Admin / Asesor)
        $asesor = User::whereIn('peran', ['admin', 'admin_super', 'superadmin'])->first();
        if (!$asesor) { $this->markTestSkipped('Butuh user admin untuk test ini.'); }

        // Kita asumsikan ada skema ID 1. Jika tidak ada kita lewati
        $skemaId = \App\Models\Skema::first()?->id ?? 1;

        // 2. Simulasi Request POST Master FR.IA.10 (pendaftaranId = 0)
        // Kita isi payload dengan data eksekusi dummy yang seolah-olah bocor
        $payload = [
            'identitas_pihak_ketiga' => [
                'nama' => 'Budi Santoso',
                'tempat_kerja' => 'PT XYZ',
                'telp' => '08123456'
            ],
            'jawaban' => [
                1 => 'Jawaban wawncara asesi...',
                2 => 'Jawaban wawncara asesi...'
            ],
            'pengalaman_teknis' => 'Asesi punya pengalaman 5 tahun',
            'testimoni_kinerja' => 'Konsisten',
            'kebutuhan_pelatihan' => 'Pelatihan lanjutan',
            'catatan_tambahan' => 'Tidak ada',
            'pertanyaan_wawancara' => [
                ['pertanyaan' => 'Pertanyaan 1 (Master)'],
                ['pertanyaan' => 'Pertanyaan 2 (Master)']
            ]
        ];

        // 3. Act
        $response = $this->actingAs($asesor)
                         ->post(route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.10', 'pendaftaranId' => 0]) . '?skema_id=' . $skemaId, $payload);

        // 4. Assert response tidak error
        $response->assertSessionHas('sukses');
        
        // 5. Assert Data di DB (Master)
        $master = SchemeMasterInstrument::where('skema_id', $skemaId)
                                        ->where('instrument_code', 'ia_10')
                                        ->first();
        
        $this->assertNotNull($master, 'Master instrumen FR.IA.10 harusnya dibuat.');
        
        // Cek bahwa data execution TIDAK ADA di additional_metadata
        $metadata = $master->additional_metadata ?? [];
        $this->assertArrayNotHasKey('identitas_pihak_ketiga', $metadata); // execution field harus ga ada
        $this->assertArrayNotHasKey('jawaban', $metadata);
        $this->assertArrayNotHasKey('pengalaman_teknis', $metadata);
    }
    public function test_master_mode_ia02_does_not_save_execution_data(): void
    {
        $admin = User::whereIn('peran', ['admin', 'admin_super', 'superadmin'])->first();
        if (!$admin) { $this->markTestSkipped('Butuh user admin untuk test ini.'); }
        $skemaId = \App\Models\Skema::first()?->id ?? 1;

        $payload = [
            'catatan' => 'Catatan pelaksanaan demonstrasi...',
            'ttd_asesi' => 'base64:ttd_dummy',
            'ttd_asesor' => 'base64:ttd_dummy'
        ];

        $response = $this->actingAs($admin)->post(route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.02', 'pendaftaranId' => 0]) . '?skema_id=' . $skemaId, $payload);
        $response->assertSessionHas('sukses');
        
        $master = SchemeMasterInstrument::where('skema_id', $skemaId)->where('instrument_code', 'ia_02')->first();
        $this->assertNotNull($master);
        $metadata = $master->additional_metadata ?? [];
        $this->assertArrayNotHasKey('ttd_asesi', $metadata);
        $this->assertArrayNotHasKey('catatan', $metadata);
    }

    public function test_master_mode_ia03_does_not_save_execution_data(): void
    {
        $admin = User::whereIn('peran', ['admin', 'admin_super', 'superadmin'])->first();
        if (!$admin) { $this->markTestSkipped('Butuh user admin untuk test ini.'); }
        $skemaId = \App\Models\Skema::first()?->id ?? 1;

        $payload = [
            'pertanyaan' => ['A1' => 'Soal Master 1'],
            'respon' => ['A1' => 'Tanggapan asesi bocor'],
            'pencapaian' => ['A1' => 'ya'],
            'umpan_balik' => 'Baik',
            'rekomendasi' => 'K'
        ];

        $response = $this->actingAs($admin)->post(route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.03', 'pendaftaranId' => 0]) . '?skema_id=' . $skemaId, $payload);
        $response->assertSessionHas('sukses');
        
        $master = SchemeMasterInstrument::where('skema_id', $skemaId)->where('instrument_code', 'ia_03')->first();
        $this->assertNotNull($master);
        $metadata = $master->additional_metadata ?? [];
        $this->assertArrayHasKey('pertanyaan', $metadata);
        $this->assertArrayNotHasKey('respon', $metadata);
        $this->assertArrayNotHasKey('pencapaian', $metadata);
        $this->assertArrayNotHasKey('umpan_balik', $metadata);
    }

    public function test_master_mode_ia04a_does_not_save_execution_data(): void
    {
        $admin = User::whereIn('peran', ['admin', 'admin_super', 'superadmin'])->first();
        if (!$admin) { $this->markTestSkipped('Butuh user admin untuk test ini.'); }
        $skemaId = \App\Models\Skema::first()?->id ?? 1;

        $payload = [
            'skenario' => 'Ini master skenario',
            'waktu_menit' => '90 Menit',
            'demonstrasi' => 'Demonstrasi proyek master',
            'waktu_demo' => '30 Menit',
            'umpan_balik' => 'Umpan balik asesi leak'
        ];

        $response = $this->actingAs($admin)->post(route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.04A', 'pendaftaranId' => 0]) . '?skema_id=' . $skemaId, $payload);
        $response->assertSessionHas('sukses');
        
        $master = SchemeMasterInstrument::where('skema_id', $skemaId)->whereIn('instrument_code', ['ia_04a', 'ia04a'])->first();
        $this->assertNotNull($master);
        $metadata = $master->additional_metadata ?? [];
        $this->assertArrayHasKey('skenario', $metadata);
        $this->assertArrayNotHasKey('umpan_balik', $metadata);
    }

    public function test_master_mode_ia07_does_not_save_execution_data(): void
    {
        $admin = User::whereIn('peran', ['admin', 'admin_super', 'superadmin'])->first();
        if (!$admin) { $this->markTestSkipped('Butuh user admin untuk test ini.'); }
        $skemaId = \App\Models\Skema::first()?->id ?? 1;

        $payload = [
            'respon_lisan' => ['Q1' => 'Tanggapan lisan asesi leak'],
            'pencapaian' => ['Q1' => 'ya'],
            'catatan' => 'Catatan bocor'
        ];

        $response = $this->actingAs($admin)->post(route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.07', 'pendaftaranId' => 0]) . '?skema_id=' . $skemaId, $payload);
        $response->assertSessionHas('sukses');
        
        $master = SchemeMasterInstrument::where('skema_id', $skemaId)->where('instrument_code', 'ia_07')->first();
        $this->assertNotNull($master);
        $metadata = $master->additional_metadata ?? [];
        $this->assertArrayNotHasKey('respon_lisan', $metadata);
        $this->assertArrayNotHasKey('pencapaian', $metadata);
    }

    public function test_master_mode_ia09_does_not_save_execution_data(): void
    {
        $admin = User::whereIn('peran', ['admin', 'admin_super', 'superadmin'])->first();
        if (!$admin) { $this->markTestSkipped('Butuh user admin untuk test ini.'); }
        $skemaId = \App\Models\Skema::first()?->id ?? 1;

        $payload = [
            'pertanyaan_wawancara' => [
                'E1' => [
                    'pertanyaan' => 'Soal wawancara',
                    'tanggapan' => 'Asesi menjawab A',
                    'keputusan' => 'M'
                ]
            ],
            'kesimpulan' => 'Kesimpulan wawancara bocor',
            'rekomendasi' => 'K'
        ];

        $response = $this->actingAs($admin)->post(route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.09', 'pendaftaranId' => 0]) . '?skema_id=' . $skemaId, $payload);
        $response->assertSessionHas('sukses');
        
        $master = SchemeMasterInstrument::where('skema_id', $skemaId)->where('instrument_code', 'ia_09')->first();
        $this->assertNotNull($master);
        $metadata = $master->additional_metadata ?? [];
        $this->assertArrayHasKey('pertanyaan_wawancara', $metadata);
        $this->assertArrayHasKey('pertanyaan', $metadata['pertanyaan_wawancara']['E1']);
        $this->assertArrayNotHasKey('tanggapan', $metadata['pertanyaan_wawancara']['E1']);
        $this->assertArrayNotHasKey('kesimpulan', $metadata);
    }

    public function test_master_mode_ia09_requires_pertanyaan_wawancara(): void
    {
        $admin = User::whereIn('peran', ['admin', 'admin_super', 'superadmin'])->first();
        if (!$admin) { $this->markTestSkipped('Butuh user admin untuk test ini.'); }
        $skemaId = \App\Models\Skema::first()?->id ?? 1;

        // 1. Submit dengan pertanyaan kosong harus ditolak
        $emptyPayload = [
            'pertanyaan_wawancara' => [
                'E1' => [
                    'pertanyaan' => '   ', // spasi kosong
                    'tanggapan' => '',
                    'keputusan' => 'M'
                ]
            ],
            'rekomendasi' => 'K'
        ];

        $response = $this->actingAs($admin)->post(route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.09', 'pendaftaranId' => 0]) . '?skema_id=' . $skemaId, $emptyPayload);
        $response->assertSessionHas('error');

        // 2. Tampilan form FR.IA.09 master baru harus kosong (tidak ada boilerplate otomatis)
        // Hapus instrumen master terlebih dahulu untuk mensimulasikan pembuatan baru
        SchemeMasterInstrument::where('skema_id', $skemaId)->whereIn('instrument_code', ['ia_09', 'ia09'])->delete();
        $viewResp = $this->actingAs($admin)->get(route('formulir.ia09', ['pendaftaranId' => 0, 'skema_id' => $skemaId]));
        $viewResp->assertStatus(200);
        $viewResp->assertDontSee('Bagaimana Anda membuktikan penerapan standar');
        $viewResp->assertSee('placeholder="Tuliskan daftar pertanyaan wawancara untuk elemen ini..."', false);
        $viewResp->assertSee('required', false);
    }

    public function test_execution_final_cannot_be_overwritten(): void
    {
        $admin = User::where('peran', 'admin_super')->first() ?? User::where('peran', 'admin')->first();
        if (!$admin) { $this->markTestSkipped(); }

        $pendaftaran = \App\Models\PendaftaranAsesi::first();
        if (!$pendaftaran) {
            $this->markTestSkipped('Butuh 1 pendaftaran asesi untuk test ini.');
        }
        
        $originalStatus = $pendaftaran->status_pendaftaran;
        $pendaftaran->status_pendaftaran = 'selesai';
        $pendaftaran->save();

        $payload = ['catatan' => 'Mencoba overwrite'];

        $response = $this->actingAs($admin)->post(route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.02', 'pendaftaranId' => $pendaftaran->id]), $payload);
        $response->assertStatus(403);
        $response->assertSee('Final');

        $pendaftaran->status_pendaftaran = $originalStatus;
        $pendaftaran->save();
    }

    public function test_unauthorized_asesor_cannot_modify_master(): void
    {
        $asesor = User::where('peran', 'asesor')->first();
        if (!$asesor) {
            $this->markTestSkipped('Butuh user asesor.');
        }
        $skemaId = \App\Models\Skema::orderBy('id', 'desc')->first()?->id ?? 999;
        
        // Hapus relasi jika ada, untuk tes "unauthorized"
        \App\Models\SkemaAsesor::where('asesor_id', $asesor->id)->where('skema_id', $skemaId)->delete();

        $payload = ['catatan' => 'Mencoba hack master'];

        $response = $this->actingAs($asesor)->post(route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.02', 'pendaftaranId' => 0]) . '?skema_id=' . $skemaId, $payload);
        $response->assertStatus(403);
    }
}
