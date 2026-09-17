@extends('tata-letak.dasbor')

@section('judul', 'Berita Acara Asesmen')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesor/dashboard-asesor.css') }}">
@endpush

@section('konten')
<div style="max-width: 1000px;" class="animasi-slide">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Berita Acara Pelaksanaan Asesmen</h1>
        <p style="color: var(--abu-teks);">Buat dan unggah dokumen Berita Acara (BA) hasil pengujian uji kompetensi</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <!-- FORM BUAT BERITA ACARA -->
        <div class="kartu">
            <h3 style="color: var(--biru-malam); margin-bottom: 1.25rem;">Form Berita Acara</h3>
            <form action="{{ route('asesor.berita-acara.simpan') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grup-form">
                    <label class="label-form">Pilih Jadwal Uji Kompetensi</label>
                    <select name="jadwal_id" class="input-control" required>
                        <option value="">-- Pilih Jadwal --</option>
                        @foreach($jadwalList as $j)
                            <option value="{{ $j->id }}">
                                {{ $j->kode_jadwal }} - {{ $j->skema->nama_skema }} ({{ date('d M Y', strtotime($j->tanggal_uji)) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grup-form">
                    <label class="label-form">Catatan & Ringkasan Pelaksanaan</label>
                    <textarea name="catatan_pelaksanaan" class="input-control" rows="4" placeholder="Uji kompetensi berjalan dengan tertib dan lancar tanpa kendala teknis." required></textarea>
                </div>

                <div class="grup-form">
                    <label class="label-form">Unggah File Lampiran BA (PDF/Docx Opsional)</label>
                    <input type="file" name="file_berita_acara" class="input-control" accept=".pdf,.doc,.docx">
                </div>

                <button type="submit" class="tombol tombol-utama" style="width: 100%; margin-top: 1rem;">
                    Generasi & Simpan Berita Acara
                </button>
            </form>
        </div>

        <!-- DAFTAR BERITA ACARA -->
        <div class="kartu">
            <h3 style="color: var(--biru-malam); margin-bottom: 1.25rem;">Riwayat Berita Acara</h3>
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th>No. BA</th>
                            <th>Jadwal / Skema</th>
                            <th>K / BK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beritaAcaraList as $ba)
                            <tr>
                                <td><strong>{{ $ba->nomor_berita_acara }}</strong></td>
                                <td>{{ $ba->jadwal->skema->nama_skema }}</td>
                                <td><span class="lencana lencana-hijau">{{ $ba->jumlah_kompeten }} K</span> / <span class="lencana lencana-merah">{{ $ba->jumlah_belum_kompeten }} BK</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--abu-teks); padding: 2rem;">Belum ada berita acara dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
