<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FR.IA.10 - Verifikasi Pihak Ketiga (Supervisor Industri)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            padding: 2rem 1rem;
            color: #1e293b;
        }
        .wadah-guest {
            max-width: 950px;
            margin: 0 auto;
        }
        .header-guest {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .canvas-sig-box {
            border: 2px dashed #94a3b8;
            background: #ffffff;
            border-radius: 8px;
            width: 100%;
            height: 140px;
            touch-action: none;
        }
        .tombol-guest {
            background: #0284c7;
            color: #ffffff;
            padding: 0.75rem 1.75rem;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s ease;
        }
        .tombol-guest:hover {
            background: #0369a1;
        }
    </style>
</head>
<body>

<div class="wadah-guest">
    
    <div class="header-guest">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span style="background: rgba(255,255,255,0.2); padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                    PORTAL VERIFIKASI SUPERVISOR INDUSTRI
                </span>
                <h1 style="font-size: 1.5rem; margin: 0.5rem 0 0.25rem 0; font-weight: 800;">Lembaga Sertifikasi Profesi (LSP)</h1>
                <p style="margin: 0; font-size: 0.9rem; color: #cbd5e1;">Konfirmasi Kinerja Nyata Calon Asesi di Tempat Kerja / Industri</p>
            </div>
            <div>
                <span style="background: #ef4444; color: white; padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 700; font-size: 0.82rem;">
                    Informasi Rahasia
                </span>
            </div>
        </div>
    </div>

    @if(session('sukses'))
        <div style="background: #ecfdf5; border: 1.5px solid #6ee7b7; color: #065f46; padding: 1.25rem 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
            <strong style="display: block; font-size: 1.1rem; margin-bottom: 0.25rem;">Terima Kasih Banyak!</strong>
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.10',
            'judulForm' => 'VPK – VERIFIKASI PIHAK KETIGA',
            'tipeDokumen' => 'Verifikasi Pihak Ketiga'
        ])

        <table class="tabel-bnsp">
            <tr>
                <td style="width: 25%; font-weight: 700; background-color: #f8fafc;">Skema Sertifikasi</td>
                <td style="width: 2%;">:</td>
                <td style="font-weight: 700; color: #0f172a;">{{ $pendaftaran->skema->nama_skema }} ({{ $pendaftaran->skema->kode_skema }})</td>
            </tr>
            <tr>
                <td style="font-weight: 700; background-color: #f8fafc;">Nama Peserta (Asesi)</td>
                <td>:</td>
                <td style="font-weight: 700; color: #0284c7; font-size: 1.05rem;">{{ $pendaftaran->asesi->nama_lengkap }}</td>
            </tr>
            <tr>
                <td style="font-weight: 700; background-color: #f8fafc;">Nama Asesor Penguji</td>
                <td>:</td>
                <td><strong>{{ $pendaftaran->asesor->nama_lengkap ?? 'Asesor LSP' }}</strong></td>
            </tr>
            <tr>
                <td style="font-weight: 700; background-color: #f8fafc;">Tanggal</td>
                <td>:</td>
                <td>{{ date('d F Y') }}</td>
            </tr>
        </table>

        <!-- PANDUAN -->
        <div class="kotak-panduan-asesor">
            <strong>PANDUAN PENGISIAN UNTUK ATASAN / SUPERVISOR INDUSTRI:</strong>
            <p style="font-size: 0.85rem; color: #334155; margin: 0.25rem 0 0 0;">
                Sebagai atasan/supervisor langsung yang mengamati kinerja peserta di lingkungan kerja, mohon kesediaan Bapak/Ibu untuk memberikan konfirmasi objektif atas pertanyaan di bawah ini guna keperluan verifikasi kompetensi standar BNSP.
            </p>
        </div>

        <form action="{{ route('formulir.ia10.guest.simpan', $token) }}" method="POST" id="form-vpk-guest">
            @csrf

            <!-- IDENTITAS SUPERVISOR -->
            <div style="font-weight: 800; font-size: 1rem; color: #0f172a; margin-bottom: 0.75rem;">
                IDENTITAS ATASAN / PENYELIA
            </div>
            <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
                <tr>
                    <td style="width: 35%; font-weight: 700; background: #f8fafc;">Nama Lengkap Supervisor / Atasan :</td>
                    <td><input type="text" name="nama_supervisor" class="input-inline-bnsp" required placeholder="Nama lengkap beserta gelar..."></td>
                </tr>
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">Jabatan / Posisi :</td>
                    <td><input type="text" name="jabatan" class="input-inline-bnsp" required placeholder="Contoh: Lead Software Engineer / Project Manager..."></td>
                </tr>
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">Nama Perusahaan / Unit Kerja :</td>
                    <td><input type="text" name="tempat_kerja" class="input-inline-bnsp" required placeholder="Nama instansi/perusahaan tempat kerja..."></td>
                </tr>
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">Nomor Telepon / WhatsApp :</td>
                    <td><input type="text" name="telepon" class="input-inline-bnsp" required placeholder="Nomor telepon aktif..."></td>
                </tr>
            </table>

            <!-- TABEL 6 PERTANYAAN KINERJA -->
            <div style="font-weight: 800; font-size: 1rem; color: #0f172a; margin-bottom: 0.75rem;">
                VERIFIKASI INTEGRITAS & KINERJA KERJA
            </div>
            <table class="tabel-bnsp" style="font-size: 0.88rem;">
                <thead>
                    <tr>
                        <th style="width: 80%;">Pernyataan Indikator Kinerja</th>
                        <th style="width: 10%; text-align: center;">Ya</th>
                        <th style="width: 10%; text-align: center;">Tidak</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1. Apakah asesi bekerja dengan mempertimbangkan Kesehatan, Keamanan dan Keselamatan Kerja (K3)?</td>
                        <td style="text-align: center;"><input type="radio" name="q_k3" value="Ya" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                        <td style="text-align: center;"><input type="radio" name="q_k3" value="Tidak" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                    </tr>
                    <tr>
                        <td>2. Apakah asesi berinteraksi dengan harmonis di dalam kelompok / tim kerjanya?</td>
                        <td style="text-align: center;"><input type="radio" name="q_tim" value="Ya" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                        <td style="text-align: center;"><input type="radio" name="q_tim" value="Tidak" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                    </tr>
                    <tr>
                        <td>3. Apakah asesi dapat mengelola tugas-tugas pekerjaan secara bersamaan (multi-tasking)?</td>
                        <td style="text-align: center;"><input type="radio" name="q_kelola" value="Ya" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                        <td style="text-align: center;"><input type="radio" name="q_kelola" value="Tidak" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                    </tr>
                    <tr>
                        <td>4. Apakah asesi dapat dengan cepat beradaptasi dengan peralatan dan teknologi/lingkungan yang baru?</td>
                        <td style="text-align: center;"><input type="radio" name="q_adaptasi" value="Ya" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                        <td style="text-align: center;"><input type="radio" name="q_adaptasi" value="Tidak" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                    </tr>
                    <tr>
                        <td>5. Apakah asesi dapat merespon dengan cepat masalah-masalah teknis yang ada di tempat kerjanya?</td>
                        <td style="text-align: center;"><input type="radio" name="q_respon" value="Ya" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                        <td style="text-align: center;"><input type="radio" name="q_respon" value="Tidak" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                    </tr>
                    <tr>
                        <td>6. Apakah Anda bersedia dihubungi jika verifikasi lebih lanjut dari pernyataan ini diperlukan oleh pihak LSP/BNSP?</td>
                        <td style="text-align: center;"><input type="radio" name="q_kontak" value="Ya" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                        <td style="text-align: center;"><input type="radio" name="q_kontak" value="Tidak" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                    </tr>
                </tbody>
            </table>

            <!-- TESTIMONI & KOMENTAR -->
            <div style="font-weight: 800; font-size: 1rem; color: #0f172a; margin: 1.5rem 0 0.75rem 0;">
                TESTIMONI & EVALUASI KERJA ASESI
            </div>
            <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
                <tr>
                    <td style="width: 40%; font-weight: 700; background: #f8fafc;">Berapa lama Anda telah bekerja bersama asesi?</td>
                    <td><input type="text" name="lama_bekerja" class="input-inline-bnsp" required placeholder="Contoh: 1 Tahun 2 Bulan"></td>
                </tr>
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">Secara keseluruhan, apakah Anda yakin asesi bekerja secara konsisten sesuai standar unit kompetensi?</td>
                    <td>
                        <textarea name="testimoni_kinerja" class="input-inline-bnsp" rows="2" required placeholder="Tuliskan ulasan kinerja..."></textarea>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">Catatan / Rekomendasi tambahan:</td>
                    <td>
                        <textarea name="catatan_tambahan" class="input-inline-bnsp" rows="2" placeholder="Catatan tambahan bila ada..."></textarea>
                    </td>
                </tr>
            </table>

            <!-- TANDA TANGAN DIGITAL SUPERVISOR -->
            <div style="border: 1px solid #334155; padding: 1.5rem; border-radius: 8px; background: #f8fafc; margin-bottom: 2rem;">
                <label style="font-weight: 800; font-size: 0.95rem; display: block; margin-bottom: 0.5rem; color: #0f172a;">
                    Tanda Tangan Digital Supervisor:
                </label>
                <canvas id="sig-canvas" class="canvas-sig-box"></canvas>
                <input type="hidden" name="tanda_tangan_supervisor" id="sig-data" required>
                <div style="margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" onclick="clearSignature()" style="background: #e2e8f0; border: 1px solid #cbd5e1; padding: 0.35rem 0.85rem; border-radius: 6px; font-size: 0.85rem; cursor: pointer;">
                        Bersihkan Tanda Tangan
                    </button>
                    <span style="font-size: 0.8rem; color: #64748b;">Tanda tangani menggunakan sentuhan layar atau mouse</span>
                </div>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="tombol-guest">
                    Kirim Konfirmasi Verifikasi Pihak Ketiga
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    // Inisialisasi Canvas Tanda Tangan
    const canvas = document.getElementById('sig-canvas');
    const ctx = canvas.getContext('2d');
    let isDrawing = false;

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
    }
    window.addEventListener('resize', resizeCanvas);
    setTimeout(resizeCanvas, 100);

    function getCoords(e) {
        const rect = canvas.getBoundingClientRect();
        if (e.touches && e.touches[0]) {
            return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
        }
        return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    }

    function startDrawing(e) {
        e.preventDefault();
        isDrawing = true;
        const coords = getCoords(e);
        ctx.beginPath();
        ctx.moveTo(coords.x, coords.y);
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        const coords = getCoords(e);
        ctx.lineTo(coords.x, coords.y);
        ctx.stroke();
        document.getElementById('sig-data').value = canvas.toDataURL();
    }

    function stopDrawing() {
        isDrawing = false;
    }

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseleave', stopDrawing);

    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);

    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('sig-data').value = '';
    }

    document.getElementById('form-vpk-guest').addEventListener('submit', function(e) {
        if (!document.getElementById('sig-data').value) {
            e.preventDefault();
            alert('Mohon bubuhkan tanda tangan digital Anda pada kotak yang disediakan.');
        }
    });
</script>

</body>
</html>
