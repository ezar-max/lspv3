<?php

namespace Tests\Feature;

use App\Models\ElemenKompetensi;
use App\Models\JawabanApl02;
use App\Models\PendaftaranAsesi;
use App\Models\Pengguna;
use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendaftaranDataIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_asesi_cannot_open_another_asesi_registration_form(): void
    {
        config(['auth.providers.users.model' => Pengguna::class]);
        $asesiA = $this->asesi('a@example.test');
        $asesiB = $this->asesi('b@example.test');
        $skema = SkemaSertifikasi::create(['kode_skema' => 'SKM-01', 'nama_skema' => 'Skema Uji']);
        $pendaftaranB = $this->pendaftaran($asesiB, $skema, 'REG-B');

        $this->actingAs($asesiA)
            ->get(route('formulir.ia01', $pendaftaranB->id))
            ->assertForbidden();
    }

    public function test_apl02_answer_is_unique_per_registration_and_element(): void
    {
        $asesi = $this->asesi('asesi@example.test');
        $skema = SkemaSertifikasi::create(['kode_skema' => 'SKM-02', 'nama_skema' => 'Skema Uji']);
        $unit = UnitKompetensi::create(['skema_id' => $skema->id, 'kode_unit' => 'UNIT-01', 'judul_unit' => 'Unit Uji']);
        $elemen = ElemenKompetensi::create(['unit_id' => $unit->id, 'nama_elemen' => 'Elemen Uji']);
        $pendaftaran = $this->pendaftaran($asesi, $skema, 'REG-A');

        JawabanApl02::create(['pendaftaran_id' => $pendaftaran->id, 'elemen_id' => $elemen->id, 'nilai_kompetensi' => 'K']);

        $this->expectException(QueryException::class);
        JawabanApl02::create(['pendaftaran_id' => $pendaftaran->id, 'elemen_id' => $elemen->id, 'nilai_kompetensi' => 'BK']);
    }

    private function asesi(string $email): Pengguna
    {
        return Pengguna::create([
            'nama_lengkap' => 'Asesi Uji',
            'email' => $email,
            'kata_sandi' => 'secret',
            'peran' => 'asesi',
        ]);
    }

    private function pendaftaran(Pengguna $asesi, SkemaSertifikasi $skema, string $nomor): PendaftaranAsesi
    {
        return PendaftaranAsesi::create([
            'nomor_pendaftaran' => $nomor,
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'tanggal_daftar' => now()->toDateString(),
        ]);
    }
}
