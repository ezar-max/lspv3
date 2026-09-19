<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokumenAsesmenInaccessibleTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $asesor;
    protected Pengguna $asesi;
    protected Pengguna $admin;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.providers.users.model' => Pengguna::class]);

        $this->asesor = Pengguna::create([
            'nama_lengkap' => 'Asesor Uji',
            'email' => 'asesor@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesor',
        ]);

        $this->asesi = Pengguna::create([
            'nama_lengkap' => 'Asesi Uji',
            'email' => 'asesi@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'asesi',
        ]);

        $this->admin = Pengguna::create([
            'nama_lengkap' => 'Admin Uji',
            'email' => 'admin@lsp.test',
            'kata_sandi' => bcrypt('secret'),
            'peran' => 'admin',
        ]);
    }

    public function test_dokumen_asesmen_routes_are_inaccessible(): void
    {
        // Hub /dokumen-asesmen
        $this->actingAs($this->asesor)->get('/dokumen-asesmen')->assertStatus(404);
        $this->actingAs($this->admin)->get('/dokumen-asesmen')->assertStatus(404);

        // FR.AK.02
        $this->actingAs($this->asesor)->get('/dokumen-asesmen/ak-02/1')->assertStatus(404);
        $this->actingAs($this->asesor)->get('/ak-02/1')->assertStatus(404);

        // FR.AK.03
        $this->actingAs($this->asesi)->get('/dokumen-asesmen/ak-03/1')->assertStatus(404);

        // FR.AK.05
        $this->actingAs($this->asesor)->get('/dokumen-asesmen/ak-05')->assertStatus(404);

        // FR.AK.06
        $this->actingAs($this->asesor)->get('/dokumen-asesmen/ak-06')->assertStatus(404);

        // FR.VA
        $this->actingAs($this->asesor)->get('/dokumen-asesmen/va')->assertStatus(404);
    }
}

