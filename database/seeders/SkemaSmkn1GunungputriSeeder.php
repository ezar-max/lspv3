<?php

namespace Database\Seeders;

use App\Models\SkemaSertifikasi;
use App\Models\UnitKompetensi;
use App\Models\ElemenKompetensi;
use App\Models\KriteriaUnjukKerja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkemaSmkn1GunungputriSeeder extends Seeder
{
    /**
     * Seed 5 Skema Sertifikasi & 32 Unit Kompetensi resmi BNSP untuk LSP SMKN 1 Gunungputri
     * Sumber: https://bnsp.go.id/lsp/smkn-1-gunungputri (No Lisensi: BNSP-LSP-2629-ID)
     */
    public function run(): void
    {
        $skemaData = [
            // =========================================================================
            // 1. TEKNIK PENGELASAN
            // =========================================================================
            [
                'kode_skema' => 'SKM-LAS-001',
                'nama_skema' => 'Skema Sertifikasi Kualifikasi II Bidang Jasa Pembuatan Barang-Barang dari Logam Subbidang Pengelasan',
                'kategori' => 'Teknik Pengelasan',
                'deskripsi' => 'Skema sertifikasi kompetensi Kualifikasi II Bidang Jasa Pembuatan Barang-Barang dari Logam Subbidang Pengelasan untuk peserta didik keahlian Teknik Pengelasan LSP SMKN 1 Gunungputri berlisensi BNSP.',
                'biaya' => 0,
                'status_aktif' => true,
                'units' => [
                    [
                        'kode_unit' => 'C.25LAS01.001.1',
                        'judul_unit' => 'Melaksanakan Persiapan Tempat Kerja',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menyiapkan area kerja dan APD pengelasan',
                                'pertanyaan' => 'Dapatkah Anda menyiapkan area kerja dan alat pelindung diri (APD) pengelasan sesuai prosedur K3?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Alat Pelindung Diri (APD) pengelasan diperiksa kelaikan dan kebersihannya sesuai standar keselamatan kerja.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Area kerja dibersihkan dari bahan-bahan yang mudah terbakar serta sirkulasi udara dipastikan aman.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menyiapkan mesin las dan peralatan bantu',
                                'pertanyaan' => 'Dapatkah Anda menyiapkan mesin las, kabel, klem, dan peralatan bantu pengelasan?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Mesin las, kabel las, dan klem masa dipasang dengan kuat dan aman sesuai spesifikasi teknis.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Bahan dan elektroda/bahan tambah disiapkan sesuai dengan instruksi kerja pengelasan.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.25LAS01.026.1',
                        'judul_unit' => 'Memperbaiki Hasil Pengelasan',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Mengidentifikasi cacat pada hasil pengelasan',
                                'pertanyaan' => 'Dapatkah Anda mengidentifikasi dan menentukan lokasi serta jenis cacat las?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Hasil pengelasan diinspeksi secara visual untuk mendeteksi cacat las seperti undercut, porosity, atau lack of fusion.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Metode perbaikan cacat las ditentukan sesuai dengan prosedur operasi standar pengelasan.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Melakukan pembersihan dan pengelasan ulang pada bagian cacat',
                                'pertanyaan' => 'Dapatkah Anda membuang bagian cacat dan melakukan perbaikan las sesuai WPS?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Bagian las yang cacat dibuang menggunakan gerinda atau gouging hingga mencapai logam dasar yang bersih.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Pengelasan ulang dilakukan sesuai WPS (Welding Procedure Specification) dan diperiksa ulang kelaikannya.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.25LAS01.028.1',
                        'judul_unit' => 'Membuat Sambungan Las Fillet Sesuai WPS Untuk Pengelasan Pelat ke Pelat, Pipa ke Pipa, dan Pelat ke Pipa Sesuai Dengan Proses Las yang Digunakan',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Melakukan perakitan dan las catat (tack welding) sambungan sudut',
                                'pertanyaan' => 'Dapatkah Anda merakit benda kerja dan melakukan las catat pada sambungan sudut?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Benda kerja diposisikan pada sudut yang tepat sesuai gambar kerja (posisi 1F, 2F, atau 3F).'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Las catat (tack weld) dilakukan pada interval yang tepat untuk mencegah distorsi selama pengelasan.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Melaksanakan pengelasan sambungan sudut (fillet)',
                                'pertanyaan' => 'Dapatkah Anda melaksanakan pengelasan fillet dengan profil dan penetrasi sesuai spesifikasi?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Parameter pengelasan (arus, voltase, kecepatan ayunan) diatur sesuai WPS.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Jalur las sudut diselesaikan dengan profil kaki las yang seragam, bebas terak, dan penetrasi memadai.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.25LAS01.029.1',
                        'judul_unit' => 'Membuat Sambungan Las Kampuh (Groove) Sesuai WPS Untuk Pengelasan Pelat ke Pelat dan Sesuai dengan Proses Las yang Digunakan',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menyiapkan kampuh las (groove preparation)',
                                'pertanyaan' => 'Dapatkah Anda menyiapkan kampuh las sesuai spesifikasi WPS?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Sudut bevel, root face, dan root gap diperiksa kesesuaiannya dengan spesifikasi prosedur las.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Pemasangan backing strip atau penyesuaian gap dilakukan secara presisi.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Melaksanakan pengelasan multi-pass kampuh (groove)',
                                'pertanyaan' => 'Dapatkah Anda melakukan pengelasan akar (root pass), pengisian (filler pass), dan penutup (capping)?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Pengelasan root pass dilakukan dengan penetrasi penuh dan fusi sempurna.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Pengisian (filler pass) dan penutup (capping) diselesaikan dengan tinggi mahkota dan lebar rigi-rigi las yang seragam.'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // 2. KIMIA INDUSTRI
            // =========================================================================
            [
                'kode_skema' => 'SKM-KMI-001',
                'nama_skema' => 'Operator Peralatan Ekstraksi dan Destilasi Bahan Alam',
                'kategori' => 'Kimia Industri',
                'deskripsi' => 'Skema sertifikasi okupasi Operator Peralatan Ekstraksi dan Destilasi Bahan Alam untuk peserta didik keahlian Kimia Industri LSP SMKN 1 Gunungputri berlisensi BNSP.',
                'biaya' => 0,
                'status_aktif' => true,
                'units' => [
                    [
                        'kode_unit' => 'C.201100.007.01',
                        'judul_unit' => 'Menghitung Neraca Bahan/Massa',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Mengidentifikasi aliran masuk dan keluar bahan',
                                'pertanyaan' => 'Dapatkah Anda mengidentifikasi aliran bahan masuk (input) dan aliran bahan keluar (output)?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Diagram alir proses (block flow diagram) diidentifikasi komponen aliran bahan masuk dan keluarnya.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Data laju alir, massa, dan fraksi massa dicatat sesuai lembar data operasional.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menghitung kesetimbangan massa proses',
                                'pertanyaan' => 'Dapatkah Anda menghitung kesetimbangan massa total dan komponen bahan?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Persamaan neraca massa total dan neraca massa komponen diformulasikan secara tepat.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Perolehan hasil (yield/rendemen) dan kehilangan bahan (losses) dihitung secara akurat.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.201100.009.01',
                        'judul_unit' => 'Menyiapkan Bahan Kimia untuk Proses Produksi',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Memeriksa identitas dan spesifikasi bahan kimia',
                                'pertanyaan' => 'Dapatkah Anda memeriksa label, kondisi kemasan, dan lembar MSDS bahan kimia?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Identitas, label bahaya, dan MSDS bahan kimia diperiksa sebelum penanganan.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Kondisi fisik dan kualitas bahan kimia dipastikan memenuhi persyaratan produksi.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menakar dan memindahkan bahan kimia',
                                'pertanyaan' => 'Dapatkah Anda menakar kuantitas bahan kimia dan memindahkannya dengan aman?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Bahan kimia ditimbang atau diukur volumenya menggunakan wadah dan instrumen yang terkalibrasi.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Pemindahan bahan kimia ke wadah proses dilakukan dengan menerapkan SOP penanganan bahan kimia berbahaya.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.201100.015.01',
                        'judul_unit' => 'Mengoperasikan Kondensor',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menyiapkan pengoperasian sistem kondensor',
                                'pertanyaan' => 'Dapatkah Anda memeriksa sirkulasi pendingin dan kesiapan kondensor?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Saluran fluida pendingin (cooling water) dan sambungan pipa diperiksa dari kebocoran.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Katup masuk dan keluar fluida pendingin diatur sesuai arah aliran kondensor.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Mengendalikan laju kondensasi',
                                'pertanyaan' => 'Dapatkah Anda mengontrol suhu dan debit fluida pendingin untuk mengoptimalkan kondensasi?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Suhu uap masuk dan cairan kondensat keluar dipantau secara berkesinambungan.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Debit pendingin disesuaikan untuk menjaga efisiensi kondensasi uap bahan alam.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.201100.019.01',
                        'judul_unit' => 'Mengoperasikan Peralatan Ekstraksi',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Mempersiapkan ekstraktor dan bahan baku alam',
                                'pertanyaan' => 'Dapatkah Anda menyiapkan tangki ekstraktor, pelarut, dan simplisia bahan alam?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Alat ekstraktor dibersihkan dan dipastikan bebas kontaminan sebelum operasi.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Simplisia/bahan alam dan pelarut (solvent) dimasukkan dengan rasio perbandingan yang tepat.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menjalankan dan memantau proses ekstraksi',
                                'pertanyaan' => 'Dapatkah Anda mengendalikan parameter suhu, waktu, pengadukan, dan pemisahan ekstrak?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Suhu pemanasan dan kecepatan pengadukan diatur sesuai instruksi kerja proses ekstraksi.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Filtrat ekstrak dipisahkan dari ampas padatan secara sempurna dan ditampung pada wadah bersih.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.201100.020.01',
                        'judul_unit' => 'Mengoperasikan Peralatan Destilasi',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menyiapkan rangkaian peralatan destilasi',
                                'pertanyaan' => 'Dapatkah Anda merangkai labu, kolom fraksinasi, pemanas, dan kondensor destilasi?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Sambungan rangkaian alat destilasi dipastikan rapat dan diberi pelumas vakum/silikon bila diperlukan.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Batu didih dimasukkan ke dalam labu destilasi untuk menghindari letupan cairan (bumping).'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Melakukan pemisahan fraksi destilasi',
                                'pertanyaan' => 'Dapatkah Anda mengontrol pemanasan sesuai titik didih dan menampung destilat?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Pemanasan dikontrol secara bertahap hingga suhu uap konstan pada titik didih fraksi target.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Destilat hasil pemisahan ditampung, diukur volume serta kemurniannya sesuai spesifikasi produk.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'M.71LAB00.033.1',
                        'judul_unit' => 'Menerapkan Keselamatan Kerja di Laboratorium/Lingkungan Kerja',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Mengidentifikasi potensi bahaya di lingkungan kimia/laboratorium',
                                'pertanyaan' => 'Dapatkah Anda mengidentifikasi potensi bahaya kimia, fisik, dan biologis di laboratorium?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Simbol bahaya bahan kimia dan rambu K3 laboratorium dipatuhi secara ketat.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'APD laboratorium lengkap (jas lab, goggle, sarung tangan, masker) digunakan selama aktivitas kerja.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Melaksanakan tanggap darurat dan penanganan limbah',
                                'pertanyaan' => 'Dapatkah Anda menangani tumpahan bahan kimia dan membuang limbah sesuai prosedur?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Tumpahan bahan kimia dinetralkan dan dibersihkan menggunakan spill kit sesuai SOP.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Limbah sisa proses diklasifikasikan dan dibuang ke wadah penampungan B3 khusus.'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // 3. REKAYASA PERANGKAT LUNAK (TEKNOLOGI INFORMASI)
            // =========================================================================
            [
                'kode_skema' => 'SKM-RPL-001',
                'nama_skema' => 'Pemrogram Junior (Junior Coder)',
                'kategori' => 'Teknologi Informasi',
                'deskripsi' => 'Skema sertifikasi okupasi Pemrogram Junior (Junior Coder) untuk peserta didik keahlian Rekayasa Perangkat Lunak (RPL) LSP SMKN 1 Gunungputri berlisensi BNSP.',
                'biaya' => 0,
                'status_aktif' => true,
                'units' => [
                    [
                        'kode_unit' => 'J.620100.004.02',
                        'judul_unit' => 'Menggunakan Struktur Data',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Mengidentifikasi konsep struktur data',
                                'pertanyaan' => 'Dapatkah Anda mengidentifikasi dan memilih struktur data yang tepat?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Konsep struktur data (array, list, stack, queue, map) diidentifikasi sesuai kebutuhan penyimpanan data program.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Kelebihan dan keterbatasan tipe struktur data dibandingkan untuk konteks penggunaan tertentu.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menerapkan struktur data pada kode program',
                                'pertanyaan' => 'Dapatkah Anda mengimplementasikan struktur data dalam kode program?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Struktur data dideklarasikan dan diinisialisasi sesuai sintaks bahasa pemrograman yang digunakan.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Operasi manipulasi data (insert, update, delete, traverse) dijalankan dengan benar tanpa eror memori.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'J.620100.009.01',
                        'judul_unit' => 'Menggunakan Spesifikasi Program',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Membaca dan memahami spesifikasi program',
                                'pertanyaan' => 'Dapatkah Anda membaca dokumen spesifikasi teknis dan kebutuhan fitur perangkat lunak?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Dokumen kebutuhan spesifikasi program dibaca dan diidentifikasi alur logika bisnisnya.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Format masukan (input), proses logika, dan keluaran (output) yang diharapkan dipetakan dengan cermat.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menyesuaikan implementasi kode dengan spesifikasi',
                                'pertanyaan' => 'Dapatkah Anda memastikan kode yang dibuat sesuai dengan batasan spesifikasi program?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Batasan fungsional dan non-fungsional diverifikasi dalam rancangan algoritma program.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Rencana pembuatan modul kode program disusun selaras dengan spesifikasi fitur.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'J.620100.010.01',
                        'judul_unit' => 'Menerapkan Perintah Eksekusi Bahasa Pemrograman Berbasis Teks, Grafik, dan Multimedia',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menyiapkan lingkungan pengembangan pemrograman',
                                'pertanyaan' => 'Dapatkah Anda menyiapkan IDE, compiler/interpreter, dan dependensi pemrograman?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'IDE/editor teks dan compiler/interpreter disiapkan sesuai lingkungan target aplikasi.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'File sumber program dan aset pendukung (teks/grafis) dikonfigurasikan pada struktur direktori proyek.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menulis dan mengeksekusi perintah pemrograman',
                                'pertanyaan' => 'Dapatkah Anda menulis kode untuk pemrosesan teks, manipulasi visual/grafis, dan multimedia?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Sintaks perintah pemrosesan data teks, grafis, atau multimedia ditulis secara valid.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Program dikompilasi atau dijalankan dengan output yang sesuai harapan tanpa pesan kegagalan.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'J.620100.016.01',
                        'judul_unit' => 'Menulis Kode dengan Prinsip Sesuai Guidelines dan Best Practices',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menerapkan coding convention dan standar penamaan',
                                'pertanyaan' => 'Dapatkah Anda menerapkan aturan penulisan kode baku (naming convention, indentasi)?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Aturan penamaan variabel, fungsi, konstanta, dan modul diterapkan secara seragam dan ekspresif.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Indentasi, format baris, dan penempatan blok kurung konsisten mengikuti panduan gaya (style guide).'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menulis kode yang bersih dan mudah dipelihara (clean code)',
                                'pertanyaan' => 'Dapatkah Anda menulis kode bersih, menghindari duplikasi kode, dan menyematkan komentar?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Ukuran fungsi/metode dijaga tetap ringkas dan mematuhi prinsip satu tanggung jawab (single responsibility).'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Komentar penjelasan disematkan pada logika yang kompleks dan duplikasi kode dieliminasi.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'J.620100.017.02',
                        'judul_unit' => 'Mengimplementasikan Pemrograman Terstruktur',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menerapkan variabel, tipe data, dan kontrol percabangan/perulangan',
                                'pertanyaan' => 'Dapatkah Anda menggunakan tipe data, percabangan (if/switch), dan perulangan (for/while)?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Tipe data primitif dan variabel dideklarasikan sesuai peruntukan alur logika.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Struktur kontrol kondisi (percabangan) dan loop (perulangan) disusun dengan kondisi terminasi yang tepat.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Membuat subrutin fungsi dan prosedur modular',
                                'pertanyaan' => 'Dapatkah Anda membuat fungsi/prosedur dengan parameter dan nilai balik yang benar?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Program dibagi menjadi subrutin/fungsi modular untuk mempermudah pelacakan dan penggunaan ulang.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Parameter fungsi, cakupan variabel (scope), dan return value diimplementasikan dengan benar.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'J.620100.023.02',
                        'judul_unit' => 'Membuat Dokumen Kode Program',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menyusun dokumentasi internal kode (inline comments/docblocks)',
                                'pertanyaan' => 'Dapatkah Anda membuat dokumentasi modul, fungsi, dan parameter dalam kode?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Blok komentar dokumentasi (docblock) ditulis menerangkan tujuan modul, parameter, dan keluaran.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Diagram alur atau pseudocode disimpan sebagai referensi teknis pengembang.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menyusun petunjuk instalasi dan pengoperasian kode (README)',
                                'pertanyaan' => 'Dapatkah Anda membuat panduan instalasi, dependensi, dan cara menjalankan aplikasi?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Petunjuk instalasi, prasyarat sistem, dan perintah eksekusi disusun dalam berkas README.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Dokumen kode program diperbarui dan disimpan dalam repositori versi kode.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'J.620100.025.02',
                        'judul_unit' => 'Melakukan Debugging',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Mengidentifikasi dan melokalisasi galat (bug)',
                                'pertanyaan' => 'Dapatkah Anda melacak pesan kesalahan syntax, runtime error, dan kelemahan logika program?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Pesan kesalahan sistem (error log / trace) dianalisis untuk mengetahui sumber masalah.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Titik kesalahan (breakpoint / logging) dipasang untuk memantau perubahan nilai variabel saat runtime.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Memperbaiki kode yang bermasalah',
                                'pertanyaan' => 'Dapatkah Anda memperbaiki baris kode dan menguji kembali untuk memastikan bug teratasi?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Perbaikan kode diterapkan pada baris yang menimbulkan galat tanpa merusak fungsionalitas lain.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Program dijalankan kembali untuk memverifikasi galat telah teratasi secara menyeluruh.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'J.620100.033.02',
                        'judul_unit' => 'Melaksanakan Pengujian Unit Program',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Merumuskan skenario dan data uji unit',
                                'pertanyaan' => 'Dapatkah Anda merancang kasus uji (test case) untuk input normal, batas, dan salah?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Kasus uji (test cases) disusun mencakup skenario input valid, batas (boundary), dan tidak valid.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Ekspektasi hasil keluaran (expected result) ditentukan untuk setiap skenario uji.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Mengeksekusi pengujian dan mencatat hasilnya',
                                'pertanyaan' => 'Dapatkah Anda menjalankan tes unit dan mendokumentasikan hasil lulus/gagal?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Pengujian unit dijalankan secara sistematis menggunakan test runner atau prosedur manual.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Hasil pengujian (status lolos/gagal) didokumentasikan dalam lembar laporan uji unit.'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // 4. TEKNIK ELEKTRONIKA INDUSTRI
            // =========================================================================
            [
                'kode_skema' => 'SKM-ELN-001',
                'nama_skema' => 'Measuring Operator',
                'kategori' => 'Teknik Elektronika',
                'deskripsi' => 'Skema sertifikasi okupasi Measuring Operator untuk peserta didik keahlian Teknik Elektronika Industri (TEI) LSP SMKN 1 Gunungputri berlisensi BNSP.',
                'biaya' => 0,
                'status_aktif' => true,
                'units' => [
                    [
                        'kode_unit' => 'C.26EPP00.016.1',
                        'judul_unit' => 'Membaca dan Mengidentifikasi Komponen Elektronika Pasif',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Membaca nilai kode warna dan angka komponen pasif',
                                'pertanyaan' => 'Dapatkah Anda membaca nilai resistansi resistor dan kapasitansi kapasitor?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Gelang warna atau kode numerik pada resistor, kapasitor, dan induktor dibaca dengan tepat.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Rating tegangan, toleransi, dan kapasitas daya komponen pasif diidentifikasi sesuai lembar spesifikasi.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menguji kelayakan komponen elektronika pasif',
                                'pertanyaan' => 'Dapatkah Anda mengukur nilai aktual komponen pasif dan menentukan kondisi baik/rusaknya?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Nilai aktual komponen diukur menggunakan LCR meter atau multimeter terkalibrasi.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Kondisi kelaikan komponen (baik, putus, hubung singkat, atau bocor) ditentukan dari deviasi nilai toleransi.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.26EPP00.017.1',
                        'judul_unit' => 'Membaca dan Mengidentifikasi Komponen Elektronika Aktif',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Mengidentifikasi jenis dan konfigurasi pinout komponen aktif',
                                'pertanyaan' => 'Dapatkah Anda membaca datasheet komponen aktif dan menentukan kaki anoda-katoda atau B-C-E?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Tipe nomor seri dioda, transistor, MOSFET, dan IC diidentifikasi melalui datasheet resmi pabrikan.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Konfigurasi kaki terminal (pinout) dipetakan sesuai orientasi fisik casing komponen.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menguji karakteristik sambungan komponen aktif',
                                'pertanyaan' => 'Dapatkah Anda menguji junction diode/transistor dengan multimeter?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Pengujian bias maju (forward bias) dan bias mundur (reverse bias) dilakukan menggunakan multimeter fungsi diode test.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Kondisi sambungan p-n diverifikasi untuk memastikan komponen aktif bekerja secara normal.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.26EPP00.010.1',
                        'judul_unit' => 'Mengoperasikan Peralatan ukur Elektronika',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Mengoperasikan multimeter analog dan digital',
                                'pertanyaan' => 'Dapatkah Anda mengukur tegangan AC/DC, arus DC, dan resistansi dengan multimeter?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Batas ukur (range selector) dipilih pada posisi yang tepat di atas estimasi nilai pengukuran.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Kalibrasi nol (zero adjustment) dilakukan sebelum pembacaan nilai ukur.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Mengoperasikan osiloskop dan generator sinyal',
                                'pertanyaan' => 'Dapatkah Anda mengukur frekuensi, periode, dan tegangan Vpp gelombang sinyal dengan osiloskop?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Probe osiloskop dikalibrasi terhadap sinyal uji referensi internal (1 kHz / 2 Vpp).'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Bentuk gelombang, amplitudo tegangan, dan frekuensi sinyal diukur serta dihitung dari skala Volt/Div dan Time/Div.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.26EPP00.001.1',
                        'judul_unit' => 'Menerapkan Prosedur Keamanan, Kesehatan dan Keselamatan Kerja (K3) Elektronika',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menerapkan proteksi terhadap bahaya listrik dan ESD',
                                'pertanyaan' => 'Dapatkah Anda mencegah bahaya sengatan listrik dan muatan listrik statis (ESD)?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Prosedur pemutusan daya (de-energize) dipastikan sebelum melakukan kontak pada sirkuit.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Gelang antistatis (ESD wrist strap) dan alas meja kerja konduktif digunakan untuk melindungi komponen sensitif.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menjaga keselamatan lingkungan kerja elektronika',
                                'pertanyaan' => 'Dapatkah Anda merespons kondisi darurat dan menggunakan alat pemadam di lab?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Penempatan APAR tipe CO2 / clean agent dan kotak P3K dipastikan bebas halangan.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Ventilasi hisap asap solder (fume extractor) diaktifkan selama proses pematerian.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.26EPP00.003.1',
                        'judul_unit' => 'Memelihara Kebersihan Tempat Kerja Elektronika',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menerapkan budaya kerja 5S/5R pada workbench',
                                'pertanyaan' => 'Dapatkah Anda menjaga kebersihan dan kerapian area meja kerja elektronika?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Meja kerja dibersihkan dari serpihan timah solder, potongan kawat, dan sisa bahan kimia flux.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Perkakas tangan dan instrumen ukur ditata rapi pada posisi penyimpanan yang ditentukan.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menjaga kebersihan dan perawatan instrumen uji',
                                'pertanyaan' => 'Dapatkah Anda merawat dan membersihkan instrumen ukur setelah pemakaian?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Layar dan probe instrumen ukur dibersihkan menggunakan kain lembut bebas serat.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Kabel daya dan kabel ukur digulung rapi tanpa tekukan tajam untuk menjaga keawetan.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.26EPP00.009.1',
                        'judul_unit' => 'Membuat dokumentasi kerusakan dan perbaikan perangkat elektronik',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Mencatat gejala dan titik kerusakan rangkaian',
                                'pertanyaan' => 'Dapatkah Anda mencatat hasil analisis kerusakan pada logbook/kartu perbaikan?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Gejala kerusakan visual dan hasil uji tegangan abnormal dicatat pada formulir inspeksi perbaikan.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Komponen yang mengalami kegagalan fungsi diidentifikasi nomor kodenya pada skema rangkaian.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menyusun laporan riwayat servis dan perbaikan',
                                'pertanyaan' => 'Dapatkah Anda membuat laporan tindakan perbaikan dan penggantian suku cadang?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Tindakan korektif (penggantian part, penyolderan ulang, kalibrasi) ditulis secara sistematis.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Laporan riwayat servis diverifikasi dan diarsipkan sesuai prosedur LSP.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.26EPP00.002.1',
                        'judul_unit' => 'Memelihara peralatan kerja elektronika',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Memeriksa kelaikan instrumen dan perkakas tangan',
                                'pertanyaan' => 'Dapatkah Anda memeriksa ujung mata solder, tang, obeng, dan baterai alat ukur?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Mata solder (soldering tip) diperiksa kebersihannya dan dilapisi timah tipis (tinning) sebelum disimpan.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Sekering pengaman internal dan daya baterai instrumen ukur dipastikan dalam kondisi prima.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Mengelola jadwal kalibrasi dan pelabelan alat',
                                'pertanyaan' => 'Dapatkah Anda memverifikasi stiker kalibrasi alat ukur dan memberi label pada alat rusak?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Masa berlaku stiker kalibrasi instrumen ukur diperiksa sebelum digunakan dalam pengujian.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Peralatan yang rusak atau tidak presisi diberi label penanda (tagging) khusus.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.26EPP00.011.1',
                        'judul_unit' => 'Mengganti Komponen Elektronika Through Hole pada PCB',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Melakukan pencabutan komponen lama (desoldering)',
                                'pertanyaan' => 'Dapatkah Anda mencabut komponen kaki through hole tanpa merusak jalur tembaga PCB?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Pemanasan timah solder dilakukan menggunakan atraktor/desoldering pump secara higienis.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Kaki komponen diangkat dari lubang PCB tanpa mengangkat jalur tembaga (copper trace/pad).'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Memasang dan menyolder komponen baru',
                                'pertanyaan' => 'Dapatkah Anda memasang komponen baru dan menyolder dengan hasil mengkilap dan konkaf?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Kaki komponen baru ditekuk dan dimasukkan sesuai orientasi polaritas pada silkscreen PCB.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Penyolderan dilakukan dengan panas dan durasi terukur menghasilkan sambungan solder berbentuk kerucut cekung yang mengkilap.'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // 5. TEKNIK PEMESINAN
            // =========================================================================
            [
                'kode_skema' => 'SKM-MSN-001',
                'nama_skema' => 'Operator Mesin Bubut',
                'kategori' => 'Teknik Pemesinan',
                'deskripsi' => 'Skema sertifikasi okupasi Operator Mesin Bubut untuk peserta didik keahlian Teknik Pemesinan LSP SMKN 1 Gunungputri berlisensi BNSP.',
                'biaya' => 0,
                'status_aktif' => true,
                'units' => [
                    [
                        'kode_unit' => 'C.28LOG07.005.2',
                        'judul_unit' => 'Membubut Dasar',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menyiapkan mesin bubut, pencekam, dan pahat bubut',
                                'pertanyaan' => 'Dapatkah Anda menyetel pahat bubut setinggi senter dan mencekam benda kerja?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Pahat bubut dipasang pada tool post dengan ketinggian tepat setinggi senter kepala lepas.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Benda kerja dicekam pada cekam tiga rahang (three jaw chuck) dengan kesentrisan yang stabil.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Melaksanakan proses pembubutan rata, muka, dan bertingkat',
                                'pertanyaan' => 'Dapatkah Anda membubut permukaan (facing) dan membubut silindris bertingkat sesuai ukuran?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Putaran mesin (RPM) dan gerak makan (feeding) diatur sesuai formula kecepatan potong bahan.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Pembubutan muka (facing) dan pembubutan lurus bertingkat dilakukan hingga mencapai dimensi toleransi gambar kerja.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.28LOG09.002.2',
                        'judul_unit' => 'Membaca Gambar Teknik',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Mengidentifikasi garis, proyeksi, dan pandangan gambar kerja',
                                'pertanyaan' => 'Dapatkah Anda membaca proyeksi Eropa/Amerika dan garis ukuran gambar mesin?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Sistem proyeksi (sudut pertama atau sudut ketiga) dan skala gambar diidentifikasi dari kepala gambar (etiket).'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Bentuk geometri dan potongan benda kerja divisualisasikan dari gambar pandangan utama.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Membaca ukuran dimensi, toleransi, dan simbol kekasaran',
                                'pertanyaan' => 'Dapatkah Anda membaca angka ukuran toleransi linier dan harga kekasaran permukaan (Ra)?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Ukuran nominal dan nilai toleransi poros/lubang diidentifikasi untuk acuan pemesinan.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Tanda pengerjaan dan simbol kekasaran permukaan (tanda segitiga/Ra) diterjemahkan ke metode pembubutan.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.28LOG12.008.2',
                        'judul_unit' => 'Mengukur Dengan Menggunakan Alat Ukur',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Mengoperasikan jangka sorong (vernier caliper)',
                                'pertanyaan' => 'Dapatkah Anda mengukur diameter luar, dalam, dan kedalaman dengan ketelitian 0.05 mm / 0.02 mm?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Garis nol skala vernier diverifikasi kesesuaiannya terhadap skala utama saat rahang tertutup.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Pengukuran dimensi diameter dan panjang benda kerja dilakukan dengan posisi tegak lurus dan tekanan konstan.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Mengoperasikan mikrometer luar (outside micrometer)',
                                'pertanyaan' => 'Dapatkah Anda mengukur diameter benda kerja dengan mikrometer ketelitian 0.01 mm?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Titik nol mikrometer diperiksa menggunakan batang kalibrasi sebelum pengukuran.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Thimble dan ratchet stop diputar perlahan hingga menyentuh benda kerja untuk pembacaan dimensi presisi.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.28LOG15.002.2',
                        'judul_unit' => 'Menerapkan Prosedur Mutu',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Melakukan pemeriksaan mutu mandiri pada proses pemesinan',
                                'pertanyaan' => 'Dapatkah Anda memeriksa kesesuaian dimensi produk secara berkala selama membubut?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Benda kerja diukur secara berkala selama langkah pembubutan kasar (roughing) dan akhir (finishing).'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Kualitas kehalusan permukaan dibandingkan dengan standar sampel kekasaran komparatif.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Menangani produk yang tidak memenuhi spesifikasi',
                                'pertanyaan' => 'Dapatkah Anda mengidentifikasi dan memisahkan produk cacat (non-conforming product)?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Benda kerja di luar batas toleransi dipisahkan dan diberi label status tidak sesuai.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Penyebab penyimpangan dimensi dicatat dan tindakan perbaikan penyetelan mesin diterapkan.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.28LOG18.001.2',
                        'judul_unit' => 'Menggunakan Perkakas Tangan',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Memilih dan memeriksa kelaikan perkakas tangan bengkel mesin',
                                'pertanyaan' => 'Dapatkah Anda memilih kikir, gergaji tangan, penitik, dan mistar baja yang aman?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Perkakas tangan dipilih sesuai spesifikasi pekerjaan kerja bangku yang akan dilakukan.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Kondisi fisik tangkai pegangan dan ketajaman mata perkakas dipastikan aman tanpa cacat.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Melakukan pekerjaan deburring dan penandaan benda kerja',
                                'pertanyaan' => 'Dapatkah Anda mengikir beram tajam (deburring) dan menandai pusat benda kerja?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Beram tajam sisa pembubutan dibersihkan menggunakan kikir halus atau scrapper.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Titik pusat benda kerja ditandai menggunakan penitik dan palu dengan akurat.'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'kode_unit' => 'C.28LOG20.003.2',
                        'judul_unit' => 'Menerapkan Prinsip-prinsip K3 di Tempat Kerja',
                        'standar_kompetensi' => 'SKKNI',
                        'elemen' => [
                            [
                                'nomor_elemen' => 1,
                                'nama_elemen' => 'Menerapkan APD keselamatan di bengkel mesin bubut',
                                'pertanyaan' => 'Dapatkah Anda menggunakan pakaian kerja, kacamata pengaman, dan sepatu safety dengan benar?',
                                'kuk' => [
                                    ['nomor' => '1.1', 'pernyataan' => 'Kacamata pelindung (safety glasses), baju bengkel (wearpack), dan sepatu safety digunakan secara tertib.'],
                                    ['nomor' => '1.2', 'pernyataan' => 'Pakaian longgar, dasi, atau aksesoris perhiasan dilepas serta rambut panjang dirapikan untuk mencegah bahaya terbelit putaran spindel.'],
                                ],
                            ],
                            [
                                'nomor_elemen' => 2,
                                'nama_elemen' => 'Mengoperasikan mesin secara aman dan mematuhi SOP darurat',
                                'pertanyaan' => 'Dapatkah Anda memastikan kunci chuck dilepas sebelum ON dan memahami fungsi tombol emergency?',
                                'kuk' => [
                                    ['nomor' => '2.1', 'pernyataan' => 'Kunci cekam (chuck key) dipastikan selalu dilepas dari chuck sebelum menyalakan sakelar utama mesin.'],
                                    ['nomor' => '2.2', 'pernyataan' => 'Fungsi tombol berhenti darurat (emergency stop) diperiksa dan siap diakses jika terjadi anomali pengoperasian.'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        DB::beginTransaction();

        try {
            foreach ($skemaData as $data) {
                // 1. Simpan / Perbarui Skema Sertifikasi
                $skema = SkemaSertifikasi::updateOrCreate(
                    ['kode_skema' => $data['kode_skema']],
                    [
                        'nama_skema' => $data['nama_skema'],
                        'kategori' => $data['kategori'],
                        'deskripsi' => $data['deskripsi'],
                        'biaya' => $data['biaya'],
                        'status_aktif' => $data['status_aktif'],
                    ]
                );

                $this->command->info("Menyimpan Skema: [{$skema->kode_skema}] {$skema->nama_skema}");

                // 2. Simpan Unit Kompetensi
                foreach ($data['units'] as $unitData) {
                    $unit = UnitKompetensi::updateOrCreate(
                        [
                            'skema_id' => $skema->id,
                            'kode_unit' => $unitData['kode_unit'],
                        ],
                        [
                            'judul_unit' => $unitData['judul_unit'],
                            'standar_kompetensi' => $unitData['standar_kompetensi'],
                        ]
                    );

                    // 3. Simpan Elemen Kompetensi & KUK
                    if (!empty($unitData['elemen'])) {
                        foreach ($unitData['elemen'] as $elemenData) {
                            $elemen = ElemenKompetensi::updateOrCreate(
                                [
                                    'unit_id' => $unit->id,
                                    'nomor_elemen' => $elemenData['nomor_elemen'],
                                ],
                                [
                                    'nama_elemen' => $elemenData['nama_elemen'],
                                    'pertanyaan_elemen' => $elemenData['pertanyaan'],
                                ]
                            );

                            if (!empty($elemenData['kuk'])) {
                                foreach ($elemenData['kuk'] as $kukData) {
                                    KriteriaUnjukKerja::updateOrCreate(
                                        [
                                            'elemen_id' => $elemen->id,
                                            'nomor_kuk' => $kukData['nomor'],
                                        ],
                                        [
                                            'pernyataan_kuk' => $kukData['pernyataan'],
                                        ]
                                    );
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            $this->command->info("Berhasil menambahkan seluruh skema, unit, elemen, dan KUK resmi BNSP LSP SMKN 1 Gunungputri.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error("Gagal melakukan seeding: " . $e->getMessage());
            throw $e;
        }
    }
}
