<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use App\Models\PengaturanSistem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with SuperAdmin and Admin accounts.
     */
    public function run(): void
    {
        // 1. Pengaturan Profil & Legalitas Lembaga
        PengaturanSistem::ambilData();

        // 2. Akun Super Administrator
        Pengguna::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'nama_lengkap' => 'Super Administrator',
                'kata_sandi' => Hash::make('admin123'),
                'peran' => 'superadmin',
                'nomor_telepon' => '081283854572',
                'nomor_registrasi' => 'REG.SPRADM.LSP.001',
                'aktif' => true,
            ]
        );

        // 3. Akun Administrator LSP
        Pengguna::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'nama_lengkap' => 'Administrator LSP',
                'kata_sandi' => Hash::make('admin123'),
                'peran' => 'admin',
                'nomor_telepon' => '081283854572',
                'nomor_registrasi' => 'REG.ADM.LSP.001',
                'aktif' => true,
            ]
        );

        // 4. Data Skema & Unit Kompetensi BNSP LSP SMKN 1 Gunungputri
        $this->call(SkemaSmkn1GunungputriSeeder::class);
    }
}

