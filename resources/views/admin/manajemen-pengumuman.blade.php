@extends('tata-letak.dasbor')

@section('judul', 'Manajemen Portal Pengumuman')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
@endpush

@section('konten')
<div style="max-width: 1100px;" class="animasi-slide">
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Manajemen Portal Berita & Pengumuman</h1>
            <p style="color: var(--abu-teks);">Publikasikan berita resmi, pengumuman uji kompetensi, dan panduan asesi</p>
        </div>
        <div>
            <button class="tombol tombol-utama" onclick="bukaModal('modalTambahPengumuman')">
                <i class="fa-solid fa-bullhorn"></i> Tulis Artikel Baru
            </button>
        </div>
    </div>

    <div class="kartu">
        <div class="tabel-wadah">
            <table class="tabel-custom">
                <thead>
                    <tr>
                        <th>Judul Berita</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Tanggal Terbit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($beritaList as $b)
                        <tr>
                            <td><strong style="color: var(--biru-malam);">{{ $b->judul }}</strong></td>
                            <td><span class="lencana lencana-biru" style="text-transform: uppercase;">{{ $b->kategori }}</span></td>
                            <td>{{ $b->penulis->nama_lengkap ?? 'Admin' }}</td>
                            <td>{{ date('d M Y', strtotime($b->tanggal_publikasi)) }}</td>
                            <td>
                                @if($b->dipublikasikan)
                                    <span class="lencana lencana-hijau">Publik</span>
                                @else
                                    <span class="lencana lencana-amber">Draft</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.pengumuman.hapus', $b->id) }}" method="POST" onsubmit="return confirm('Hapus artikel ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tombol tombol-bahaya tombol-sm"><i class="fa-solid fa-trash"></i> Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--abu-teks); padding: 2rem;">Belum ada artikel dipublikasikan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $beritaList->links() }}
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PENGUMUMAN -->
<div class="modal-overlay" id="modalTambahPengumuman">
    <div class="modal-konten">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="color: var(--biru-malam);">Tulis Berita / Pengumuman Baru</h3>
            <button onclick="tutupModal('modalTambahPengumuman')" style="background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('admin.pengumuman.simpan') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grup-form">
                <label class="label-form">Judul Berita / Pengumuman</label>
                <input type="text" name="judul" class="input-control" required placeholder="Judul utama artikel...">
            </div>

            <div class="grup-form">
                <label class="label-form">Kategori</label>
                <select name="kategori" class="input-control" required>
                    <option value="berita">Berita Vokasi</option>
                    <option value="pengumuman">Pengumuman Asesmen</option>
                    <option value="panduan">Panduan Asesi</option>
                </select>
            </div>

            <div class="grup-form">
                <label class="label-form">Ringkasan Singkat</label>
                <input type="text" name="ringkasan" class="input-control" placeholder="1-2 kalimat ringkasan artikel...">
            </div>

            <div class="grup-form">
                <label class="label-form">Konten Lengkap</label>
                <textarea name="konten" class="input-control" rows="6" required placeholder="Tuliskan isi berita..."></textarea>
            </div>

            <div class="grup-form" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="dipublikasikan" value="1" id="dipublikasikan" checked>
                <label for="dipublikasikan" style="font-size: 0.9rem; font-weight: 600;">Langsung Publikasikan di Portal</label>
            </div>

            <div style="margin-top: 1.5rem; text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModal('modalTambahPengumuman')">Batal</button>
                <button type="submit" class="tombol tombol-utama">Publikasikan Artikel</button>
            </div>
        </form>
    </div>
</div>
@endsection
