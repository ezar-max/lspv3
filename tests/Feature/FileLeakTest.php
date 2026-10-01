<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\ProfilAsesi;
use App\Models\DokumenAsesi;

class FileLeakTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_file_deleted_when_reupload_apl01()
    {
        Storage::fake('public');

        $asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi',
            'email' => 'asesi@test.com',
            'nomor_telepon' => '081234',
            'kata_sandi' => bcrypt('password'),
            'peran' => 'asesi',
            'aktif' => true,
        ]);
        $profil = ProfilAsesi::create([
            'pengguna_id' => $asesi->id,
            'nik' => '1234567890123456',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'pendidikan_terakhir' => 'SMA',
            'alamat' => 'Jalan A',
        ]);

        $skema = SkemaSertifikasi::create([
            'kode_skema' => 'SKM-001',
            'nama_skema' => 'Skema Dummy',
            'jenis_skema' => 'KKNI',
            'jumlah_unit' => 5,
        ]);

        $pendaftaran = PendaftaranAsesi::create([
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'nomor_pendaftaran' => 'REG-1234',
            'tanggal_daftar' => now(),
            'status_pendaftaran' => 'draft',
        ]);

        // Upload first time
        $file1 = UploadedFile::fake()->image('rapor1.jpg');
        $payload1 = [
            'pendaftaran_id' => $pendaftaran->id,
            'skema_id' => $skema->id,
            'file_rapor' => $file1,
            'ajukan' => '1',
            'nama_lengkap' => 'Asesi',
            'nik' => '1234567890123456',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jalan A',
            'nomor_telepon' => '081234',
            'tujuan_asesmen' => 'Sertifikasi',
        ];
        $response1 = $this->actingAs($asesi)->post(route('asesi.tahapan.apl01'), $payload1);

        if ($response1->baseResponse instanceof \Illuminate\Http\RedirectResponse && session()->has('errors')) {
            dd(session('errors'));
        }
        $response1->assertRedirect();
        
        $dok1 = DokumenAsesi::where('pendaftaran_id', $pendaftaran->id)
            ->where('jenis_dokumen', 'Ijazah / Rapor Terakhir')
            ->first();
            
        $this->assertNotNull($dok1);
        $oldPath = str_replace('storage/', '', $dok1->file_path);
        Storage::disk('public')->assertExists($oldPath);

        // Sleep 1 second so time() changes
        sleep(1);

        // Upload second time
        $file2 = UploadedFile::fake()->image('rapor2.jpg');
        $payload2 = $payload1;
        $payload2['file_rapor'] = $file2;
        $response2 = $this->actingAs($asesi)->post(route('asesi.tahapan.apl01'), $payload2);

        $response2->assertRedirect();

        // Check if old file deleted
        Storage::disk('public')->assertMissing($oldPath);
        
        $dok2 = DokumenAsesi::where('pendaftaran_id', $pendaftaran->id)
            ->where('jenis_dokumen', 'Ijazah / Rapor Terakhir')
            ->first();
            
        $this->assertNotNull($dok2);
        $newPath = str_replace('storage/', '', $dok2->file_path);
        Storage::disk('public')->assertExists($newPath);
    }
}
