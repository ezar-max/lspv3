@extends('tata-letak.dasbor')

@section('judul', 'Penjadwalan Asesmen')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
@endpush

@section('konten')
<div style="max-width: 1100px;" class="animasi-slide">
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Manajemen Penjadwalan & Penugasan Asesor</h1>
            <p style="color: var(--abu-teks);">Atur jadwal uji kompetensi, lokasi TUK, dan penugasan Asesor penguji</p>
        </div>
        <div>
            <button class="tombol tombol-utama" onclick="bukaModal('modalTambahJadwal')">
                <i class="fa-solid fa-calendar-plus"></i> Buat Jadwal Uji Baru
            </button>
        </div>
    </div>

    <div class="kartu">
        <div class="tabel-wadah">
            <table class="tabel-custom">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Skema Sertifikasi</th>
                        <th>Asesor Penguji</th>
                        <th>Tanggal & Waktu</th>
                        <th>Lokasi TUK</th>
                        <th>Kuota</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalList as $j)
                        <tr>
                            <td><span class="font-mono">{{ $j->kode_jadwal }}</span></td>
                            <td style="font-weight: 600; color: var(--biru-malam);">{{ $j->skema->nama_skema }}</td>
                            <td><strong style="color: var(--biru-malam);">{{ $j->asesor->nama_lengkap }}</strong></td>
                            <td>{{ date('d M Y', strtotime($j->tanggal_uji)) }}<br><small style="color: var(--abu-teks);">{{ substr($j->waktu_mulai, 0, 5) }} - {{ substr($j->waktu_selesai, 0, 5) }} WIB</small></td>
                            <td>{{ $j->nama_tuk }}</td>
                            <td><span style="font-weight: 600;">{{ $j->kuota }}</span> <span style="color: var(--abu-teks); font-size: 0.8rem;">Asesi</span></td>
                            <td>
                                @if($j->status_jadwal === 'berlangsung')
                                    <span class="lencana lencana-hijau">
                                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: #10b981;"></span>
                                        Aktif (Berlangsung)
                                    </span>
                                @elseif($j->status_jadwal === 'selesai')
                                    <span class="lencana" style="background-color: var(--biru-bg); color: var(--abu-teks); border: 1px solid var(--biru-soft);">Selesai</span>
                                @elseif($j->status_jadwal === 'dibatalkan')
                                    <span class="lencana lencana-merah">Dibatalkan</span>
                                @else
                                    <span class="lencana lencana-biru">Terjadwal</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.4rem; align-items: center;">
                                    <button type="button" 
                                            class="tombol tombol-sekunder tombol-sm"
                                            title="Edit Jadwal"
                                            onclick='bukaModalEditJadwal({
                                                id: {{ $j->id }},
                                                kode_jadwal: "{{ addslashes($j->kode_jadwal) }}",
                                                skema_id: {{ $j->skema_id }},
                                                asesor_id: {{ $j->asesor_id }},
                                                nama_tuk: "{{ addslashes($j->nama_tuk) }}",
                                                tanggal_uji: "{{ $j->tanggal_uji }}",
                                                waktu_mulai: "{{ substr($j->waktu_mulai, 0, 5) }}",
                                                waktu_selesai: "{{ substr($j->waktu_selesai, 0, 5) }}",
                                                kuota: {{ $j->kuota }},
                                                status_jadwal: "{{ $j->status_jadwal }}"
                                            })'>
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <form action="{{ route('admin.jadwal.hapus', $j->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal uji #{{ $j->kode_jadwal }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tombol tombol-bahaya tombol-sm" title="Hapus Jadwal"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--abu-teks); padding: 2rem;">Belum ada jadwal uji kompetensi terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $jadwalList->links() }}
        </div>
    </div>
</div>

<!-- MODAL TAMBAH JADWAL -->
<div class="modal-overlay" id="modalTambahJadwal">
    <div class="modal-konten">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="color: var(--biru-malam);">Buat Jadwal Uji & Penugasan Asesor</h3>
            <button onclick="tutupModal('modalTambahJadwal')" style="background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('admin.jadwal.simpan') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grup-form">
                    <label class="label-form">Kode Jadwal</label>
                    <input type="text" name="kode_jadwal" class="input-control" value="JDW-{{ date('Ymd') }}-{{ rand(10,99) }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Pilih Skema</label>
                    <select name="skema_id" id="pilih_skema_jadwal" class="input-control" required onchange="filterAsesorBySkema()">
                        <option value="">-- Pilih Skema Sertifikasi --</option>
                        @foreach($skemaOptions as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_skema }} ({{ $s->kode_skema }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grup-form">
                <label class="label-form">Tugaskan Asesor Penguji</label>
                <select name="asesor_id" id="pilih_asesor_jadwal" class="input-control" required>
                    <option value="">-- Pilih Skema Terlebih Dahulu --</option>
                    @foreach($asesorOptions as $a)
                        <option value="{{ $a->id }}" data-skema-id="{{ $a->skema_id ?? '' }}">
                            {{ $a->nama_lengkap }} ({{ $a->nomor_registrasi ? 'Reg: ' . $a->nomor_registrasi : $a->email }})
                        </option>
                    @endforeach
                </select>
                <small id="pesan_asesor_kosong" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.35rem; display: none;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Tidak ada asesor dengan role skema ini. Harap atur role skema asesor di <a href="{{ route('admin.manajemen-asesor') }}" target="_blank" style="color: var(--biru-utama); text-decoration: underline; font-weight: bold;">Manajemen Asesor</a> terlebih dahulu.
                </small>
                <small id="pesan_asesor_info" style="color: #15803d; font-size: 0.8rem; margin-top: 0.35rem; display: none;">
                    <i class="fa-solid fa-circle-check"></i> <span id="text_asesor_count">0</span> asesor ditemukan untuk skema ini.
                </small>
            </div>

            <div class="grup-form">
                <label class="label-form">Nama Tempat Uji Kompetensi (TUK)</label>
                <input type="text" name="nama_tuk" class="input-control" placeholder="contoh: Lab Komputer RPL 1" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="grup-form">
                    <label class="label-form">Tanggal Uji</label>
                    <input type="date" name="tanggal_uji" class="input-control" min="{{ date('Y-m-d') }}" value="{{ old('tanggal_uji') }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Waktu Mulai</label>
                    <input type="time" name="waktu_mulai" class="input-control" value="08:00" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Waktu Selesai</label>
                    <input type="time" name="waktu_selesai" class="input-control" value="16:00" required>
                </div>
            </div>

            <div class="grup-form">
                <label class="label-form">Kapasitas Kuota Asesi (Standar: 10 Asesi / Asesor / Hari)</label>
                <input type="number" name="kuota" class="input-control" value="10" min="1" max="50" required>
                <small style="color: var(--abu-teks); font-size: 0.8rem; margin-top: 0.25rem; display: block;">
                    * Disarankan 10 asesi per sesi/hari per asesor penguji sesuai standar beban kerja asesmen BNSP.
                </small>
            </div>

            <div style="margin-top: 1.5rem; text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModal('modalTambahJadwal')">Batal</button>
                <button type="submit" class="tombol tombol-utama">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT JADWAL -->
<div class="modal-overlay" id="modalEditJadwal">
    <div class="modal-konten" style="max-width: 650px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-b: 1px solid var(--biru-soft); padding-bottom: 0.75rem;">
            <div>
                <h3 style="color: var(--biru-malam); margin: 0; font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-calendar-check" style="color: var(--biru-utama);"></i> Edit Jadwal Asesmen
                </h3>
                <p style="color: var(--abu-teks); font-size: 0.85rem; margin-top: 0.25rem; margin-bottom: 0;">
                    Perbarui data sesi uji kompetensi, penugasan asesor, dan status pelaksanaan
                </p>
            </div>
            <button type="button" onclick="tutupModal('modalEditJadwal')" style="background: none; border: none; font-size: 1.5rem; color: var(--abu-teks); cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form id="formEditJadwal" method="POST" action="">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grup-form">
                    <label class="label-form">Kode Jadwal</label>
                    <input type="text" name="kode_jadwal" id="edit_kode_jadwal" class="input-control" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Status Pelaksanaan</label>
                    <select name="status_jadwal" id="edit_status_jadwal" class="input-control" required>
                        <option value="terjadwal">Terjadwal (Belum Mulai)</option>
                        <option value="berlangsung">Aktif (Sedang Berlangsung)</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
            </div>

            <div class="grup-form">
                <label class="label-form">Pilih Skema Sertifikasi</label>
                <select name="skema_id" id="edit_skema_id" class="input-control" required onchange="filterEditAsesorBySkema()">
                    <option value="">-- Pilih Skema Sertifikasi --</option>
                    @foreach($skemaOptions as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_skema }} ({{ $s->kode_skema }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grup-form">
                <label class="label-form">Tugaskan Asesor Penguji</label>
                <select name="asesor_id" id="edit_asesor_id" class="input-control" required>
                    <option value="">-- Pilih Skema Terlebih Dahulu --</option>
                    @foreach($asesorOptions as $a)
                        <option value="{{ $a->id }}" data-skema-id="{{ $a->skema_id ?? '' }}">
                            {{ $a->nama_lengkap }} ({{ $a->nomor_registrasi ? 'Reg: ' . $a->nomor_registrasi : $a->email }})
                        </option>
                    @endforeach
                </select>
                <small id="edit_pesan_asesor_kosong" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.35rem; display: none;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Tidak ada asesor dengan role skema ini. Harap atur role skema asesor di <a href="{{ route('admin.manajemen-asesor') }}" target="_blank" style="color: var(--biru-utama); text-decoration: underline; font-weight: bold;">Manajemen Asesor</a> terlebih dahulu.
                </small>
                <small id="edit_pesan_asesor_info" style="color: #15803d; font-size: 0.8rem; margin-top: 0.35rem; display: none;">
                    <i class="fa-solid fa-circle-check"></i> <span id="edit_text_asesor_count">0</span> asesor ditemukan untuk skema ini.
                </small>
            </div>

            <div class="grup-form">
                <label class="label-form">Tempat Uji Kompetensi (TUK)</label>
                <input type="text" name="nama_tuk" id="edit_nama_tuk" class="input-control" required placeholder="contoh: Lab Komputer RPL 1">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="grup-form">
                    <label class="label-form">Tanggal Uji</label>
                    <input type="date" name="tanggal_uji" id="edit_tanggal_uji" class="input-control" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Waktu Mulai</label>
                    <input type="time" name="waktu_mulai" id="edit_waktu_mulai" class="input-control" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Waktu Selesai</label>
                    <input type="time" name="waktu_selesai" id="edit_waktu_selesai" class="input-control" required>
                </div>
            </div>

            <div class="grup-form">
                <label class="label-form">Kapasitas Kuota Asesi (Standar: 10 Asesi / Asesor / Hari)</label>
                <input type="number" name="kuota" id="edit_kuota" class="input-control" value="10" min="1" max="50" required>
                <small style="color: var(--abu-teks); font-size: 0.8rem; margin-top: 0.25rem; display: block;">
                    * Disarankan 10 asesi per sesi/hari per asesor penguji sesuai standar beban kerja asesmen BNSP.
                </small>
            </div>

            <div style="margin-top: 1.5rem; text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModal('modalEditJadwal')">Batal</button>
                <button type="submit" class="tombol tombol-utama">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var selectSkema = document.getElementById('pilih_skema_jadwal');
        var selectAsesor = document.getElementById('pilih_asesor_jadwal');
        var pesanKosong = document.getElementById('pesan_asesor_kosong');
        var pesanInfo = document.getElementById('pesan_asesor_info');
        var textAsesorCount = document.getElementById('text_asesor_count');

        // Modal Edit Elems
        var editSelectSkema = document.getElementById('edit_skema_id');
        var editSelectAsesor = document.getElementById('edit_asesor_id');
        var editPesanKosong = document.getElementById('edit_pesan_asesor_kosong');
        var editPesanInfo = document.getElementById('edit_pesan_asesor_info');
        var editTextAsesorCount = document.getElementById('edit_text_asesor_count');

        // Ambil dan simpan seluruh data opsi asesor dari DOM
        var daftarAsesor = [];
        var srcSelect = selectAsesor || editSelectAsesor;
        if (srcSelect) {
            for (var i = 0; i < srcSelect.options.length; i++) {
                var opt = srcSelect.options[i];
                if (opt.value) {
                    daftarAsesor.push({
                        value: opt.value,
                        text: opt.text,
                        skemaId: opt.getAttribute('data-skema-id') || ''
                    });
                }
            }
        }

        // Handler Filter Asesor Modal Tambah
        window.filterAsesorBySkema = function() {
            if (!selectSkema || !selectAsesor) return;
            var skemaDipilih = selectSkema.value;
            selectAsesor.innerHTML = '';

            if (!skemaDipilih) {
                var defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.text = '-- Pilih Skema Terlebih Dahulu --';
                selectAsesor.appendChild(defaultOpt);
                if (pesanKosong) pesanKosong.style.display = 'none';
                if (pesanInfo) pesanInfo.style.display = 'none';
                return;
            }

            var asesorTersaring = daftarAsesor.filter(function(a) {
                return String(a.skemaId) === String(skemaDipilih);
            });

            if (asesorTersaring.length === 0) {
                var kosongOpt = document.createElement('option');
                kosongOpt.value = '';
                kosongOpt.text = '-- Tidak ada asesor dengan role skema ini --';
                selectAsesor.appendChild(kosongOpt);

                if (pesanKosong) pesanKosong.style.display = 'block';
                if (pesanInfo) pesanInfo.style.display = 'none';
            } else {
                var pilihOpt = document.createElement('option');
                pilihOpt.value = '';
                pilihOpt.text = '-- Pilih Asesor Penguji (' + asesorTersaring.length + ' Asesor Tersedia) --';
                selectAsesor.appendChild(pilihOpt);

                asesorTersaring.forEach(function(a) {
                    var opt = document.createElement('option');
                    opt.value = a.value;
                    opt.text = a.text;
                    selectAsesor.appendChild(opt);
                });

                if (pesanKosong) pesanKosong.style.display = 'none';
                if (pesanInfo && textAsesorCount) {
                    textAsesorCount.textContent = asesorTersaring.length;
                    pesanInfo.style.display = 'block';
                }
            }
        };

        // Handler Filter Asesor Modal Edit
        window.filterEditAsesorBySkema = function(selectedAsesorId = null) {
            if (!editSelectSkema || !editSelectAsesor) return;
            var skemaDipilih = editSelectSkema.value;
            editSelectAsesor.innerHTML = '';

            if (!skemaDipilih) {
                var defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.text = '-- Pilih Skema Terlebih Dahulu --';
                editSelectAsesor.appendChild(defaultOpt);
                if (editPesanKosong) editPesanKosong.style.display = 'none';
                if (editPesanInfo) editPesanInfo.style.display = 'none';
                return;
            }

            var asesorTersaring = daftarAsesor.filter(function(a) {
                return String(a.skemaId) === String(skemaDipilih);
            });

            if (asesorTersaring.length === 0) {
                var kosongOpt = document.createElement('option');
                kosongOpt.value = '';
                kosongOpt.text = '-- Tidak ada asesor dengan role skema ini --';
                editSelectAsesor.appendChild(kosongOpt);

                if (editPesanKosong) editPesanKosong.style.display = 'block';
                if (editPesanInfo) editPesanInfo.style.display = 'none';
            } else {
                var pilihOpt = document.createElement('option');
                pilihOpt.value = '';
                pilihOpt.text = '-- Pilih Asesor Penguji (' + asesorTersaring.length + ' Asesor Tersedia) --';
                editSelectAsesor.appendChild(pilihOpt);

                asesorTersaring.forEach(function(a) {
                    var opt = document.createElement('option');
                    opt.value = a.value;
                    opt.text = a.text;
                    if (selectedAsesorId && String(a.value) === String(selectedAsesorId)) {
                        opt.selected = true;
                    }
                    editSelectAsesor.appendChild(opt);
                });

                if (editPesanKosong) editPesanKosong.style.display = 'none';
                if (editPesanInfo && editTextAsesorCount) {
                    editTextAsesorCount.textContent = asesorTersaring.length;
                    editPesanInfo.style.display = 'block';
                }
            }
        };

        // Handler Buka Modal Edit Jadwal dengan data yang dipilih
        window.bukaModalEditJadwal = function(data) {
            var form = document.getElementById('formEditJadwal');
            if (!form) return;
            form.action = "{{ url('admin/jadwal') }}/" + data.id + "/ubah";

            document.getElementById('edit_kode_jadwal').value = data.kode_jadwal || '';
            document.getElementById('edit_status_jadwal').value = data.status_jadwal || 'terjadwal';
            document.getElementById('edit_nama_tuk').value = data.nama_tuk || '';
            document.getElementById('edit_tanggal_uji').value = data.tanggal_uji || '';
            document.getElementById('edit_waktu_mulai').value = data.waktu_mulai || '08:00';
            document.getElementById('edit_waktu_selesai').value = data.waktu_selesai || '16:00';
            document.getElementById('edit_kuota').value = data.kuota || 10;

            if (editSelectSkema) {
                editSelectSkema.value = data.skema_id || '';
                window.filterEditAsesorBySkema(data.asesor_id);
            }

            bukaModal('modalEditJadwal');
        };

        // Inisialisasi awal saat load
        filterAsesorBySkema();
    });
</script>
@endpush
