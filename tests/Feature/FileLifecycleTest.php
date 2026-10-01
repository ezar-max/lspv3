<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\PendaftaranAsesi;
use App\Models\SkemaSertifikasi;
use App\Models\DokumenAsesi;

class FileLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function setupPendaftaran()
    {
        $user = new User();
        $user->nama_lengkap = 'Test Asesi';
        $user->email = 'test' . uniqid() . '@example.com';
        $user->kata_sandi = bcrypt('password');
        $user->peran = 'asesi';
        $user->save();

        $skema = new SkemaSertifikasi();
        $skema->kode_skema = 'SKM-' . time();
        $skema->nama_skema = 'Skema Uji';
        $skema->save();

        $asesor = new User();
        $asesor->nama_lengkap = 'Test Asesor';
        $asesor->email = 'asesor' . uniqid() . '@example.com';
        $asesor->kata_sandi = bcrypt('password');
        $asesor->peran = 'asesor';
        $asesor->save();

        $jadwal = new \App\Models\JadwalAsesmen();
        $jadwal->kode_jadwal = 'JDW-' . time();
        $jadwal->skema_id = $skema->id;
        $jadwal->asesor_id = $asesor->id;
        $jadwal->nama_tuk = 'TUK Test';
        $jadwal->tanggal_uji = now()->format('Y-m-d');
        $jadwal->waktu_mulai = now()->subHours(1)->format('H:i:s');
        $jadwal->waktu_selesai = now()->addHours(2)->format('H:i:s');
        $jadwal->status_jadwal = 'berlangsung';
        $jadwal->save();

        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $user->id,
            'skema_id' => $skema->id,
            'jadwal_id' => $jadwal->id,
            'nomor_pendaftaran' => 'REG-' . time(),
            'tanggal_daftar' => now()->format('Y-m-d'),
            'status_pendaftaran' => 'menunggu_verifikasi',
            'status_ak01' => 'disetujui',
            'tanda_tangan_asesi_ak01' => 'dummy',
            'tanda_tangan_asesor_ak01' => 'dummy',
        ]);

        \App\Models\Mapa01::create([
            'pendaftaran_id' => $pendaftaran->id,
            'skema_id' => $skema->id,
            'asesor_id' => $asesor->id,
            'status_mapa' => 'selesai',
        ]);

        \App\Models\Mapa02::create([
            'pendaftaran_id' => $pendaftaran->id,
            'skema_id' => $skema->id,
            'asesor_id' => $asesor->id,
            'status_mapa' => 'selesai',
        ]);

        return [$user, $pendaftaran];
    }

    public function test_upload_ia02_successful_replacement()
    {
        [$user, $pendaftaran] = $this->setupPendaftaran();
        
        // Setup existing file
        $oldFile = UploadedFile::fake()->create('old_doc.pdf', 100);
        $oldPath = $oldFile->storeAs('dokumen-asesi', 'old_doc.pdf', 'public');
        
        DokumenAsesi::create([
            'pendaftaran_id' => $pendaftaran->id,
            'jenis_dokumen' => 'Hasil Proyek / Laporan Praktik FR.IA.02',
            'nama_dokumen' => 'old_doc.pdf',
            'file_path' => 'storage/' . $oldPath,
            'ukuran_file' => 100,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($oldPath));

        $this->actingAs($user);
        
        $newFile = UploadedFile::fake()->create('new_doc.pdf', 200);

        $response = $this->post(route('asesi.ujian.upload-ia02'), [
            'pendaftaran_id' => $pendaftaran->id,
            'file_praktik' => $newFile,
            'catatan_praktik' => 'Test replacement',
        ]);

        $response->dump();
        $response->assertSessionHas('sukses');

        $dokumen = DokumenAsesi::where('pendaftaran_id', $pendaftaran->id)->first();
        
        // Assert DB updated
        $this->assertStringContainsString('new_doc.pdf', $dokumen->nama_dokumen);
        $this->assertNotEquals('storage/' . $oldPath, $dokumen->file_path);

        $newPath = str_replace('storage/', '', $dokumen->file_path);
        
        // Assert new file exists
        $this->assertTrue(Storage::disk('public')->exists($newPath));
        
        // Assert old file deleted
        $this->assertFalse(Storage::disk('public')->exists($oldPath));
    }

    public function test_upload_ia02_new_upload_failure_retains_old_file()
    {
        [$user, $pendaftaran] = $this->setupPendaftaran();
        
        $oldFile = UploadedFile::fake()->create('old_doc.pdf', 100);
        $oldPath = $oldFile->storeAs('dokumen-asesi', 'old_doc.pdf', 'public');
        
        $docLama = DokumenAsesi::create([
            'pendaftaran_id' => $pendaftaran->id,
            'jenis_dokumen' => 'Hasil Proyek / Laporan Praktik FR.IA.02',
            'nama_dokumen' => 'old_doc.pdf',
            'file_path' => 'storage/' . $oldPath,
            'ukuran_file' => 100,
        ]);

        $this->actingAs($user);
        
        // Mock UploadedFile agar storeAs melempar exception atau mereturn false
        $newFile = \Mockery::mock(UploadedFile::fake()->create('fail_doc.pdf', 200))->makePartial();
        $newFile->shouldReceive('storeAs')->andReturn(false);

        $response = $this->post(route('asesi.ujian.upload-ia02'), [
            'pendaftaran_id' => $pendaftaran->id,
            'file_praktik' => $newFile,
            'catatan_praktik' => 'Test fail',
        ]);

        $response->assertSessionHas('error');

        // Assert old file still exists physically
        $this->assertTrue(Storage::disk('public')->exists($oldPath));

        // Assert DB still points to old file
        $dokumen = DokumenAsesi::where('pendaftaran_id', $pendaftaran->id)->first();
        $this->assertEquals($docLama->file_path, $dokumen->file_path);
    }

    public function test_upload_ia02_database_failure_cleans_up_new_file()
    {
        [$user, $pendaftaran] = $this->setupPendaftaran();
        
        $oldFile = UploadedFile::fake()->create('old_doc.pdf', 100);
        $oldPath = $oldFile->storeAs('dokumen-asesi', 'old_doc.pdf', 'public');
        
        $docLama = DokumenAsesi::create([
            'pendaftaran_id' => $pendaftaran->id,
            'jenis_dokumen' => 'Hasil Proyek / Laporan Praktik FR.IA.02',
            'nama_dokumen' => 'old_doc.pdf',
            'file_path' => 'storage/' . $oldPath,
            'ukuran_file' => 100,
        ]);

        $this->actingAs($user);
        
        // Simulasikan DB gagal dengan event
        \App\Models\DokumenAsesi::saving(function ($model) {
            throw new \Exception('Simulated DB failure');
        });

        $newFile = UploadedFile::fake()->create('db_fail_doc.pdf', 200);

        $response = $this->post(route('asesi.ujian.upload-ia02'), [
            'pendaftaran_id' => $pendaftaran->id,
            'file_praktik' => $newFile,
            'catatan_praktik' => 'Test DB fail',
        ]);

        $response->assertSessionHas('error');

        // Assert old file still exists physically
        $this->assertTrue(Storage::disk('public')->exists($oldPath));

        // Assert DB still points to old file
        $dokumen = DokumenAsesi::where('pendaftaran_id', $pendaftaran->id)->first();
        $this->assertEquals($docLama->file_path, $dokumen->file_path);

        // Assert new file is deleted (cleanup works)
        $filesInDir = Storage::disk('public')->files('dokumen-asesi');
        $this->assertCount(1, $filesInDir); // Hanya old file yang tersisa
        $this->assertEquals($oldPath, $filesInDir[0]);
    }

    public function test_upload_ia02_missing_old_file_proceeds()
    {
        [$user, $pendaftaran] = $this->setupPendaftaran();
        
        $oldPath = 'dokumen-asesi/missing_file.pdf';
        
        DokumenAsesi::create([
            'pendaftaran_id' => $pendaftaran->id,
            'jenis_dokumen' => 'Hasil Proyek / Laporan Praktik FR.IA.02',
            'nama_dokumen' => 'missing_file.pdf',
            'file_path' => 'storage/' . $oldPath,
            'ukuran_file' => 100,
        ]);

        $this->assertFalse(Storage::disk('public')->exists($oldPath));

        $this->actingAs($user);
        
        $newFile = UploadedFile::fake()->create('new_doc.pdf', 200);

        $response = $this->post(route('asesi.ujian.upload-ia02'), [
            'pendaftaran_id' => $pendaftaran->id,
            'file_praktik' => $newFile,
            'catatan_praktik' => 'Test missing old',
        ]);

        $response->assertSessionHas('sukses');

        $dokumen = DokumenAsesi::where('pendaftaran_id', $pendaftaran->id)->first();
        
        $this->assertStringContainsString('new_doc.pdf', $dokumen->nama_dokumen);
        
        $newPath = str_replace('storage/', '', $dokumen->file_path);
        $this->assertTrue(Storage::disk('public')->exists($newPath));
    }
}

