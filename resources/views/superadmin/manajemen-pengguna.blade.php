@extends('tata-letak.dasbor')

@section('judul', 'Manajemen Pengguna CRUD')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/superadmin/dashboard-superadmin.css') }}">
@endpush

@section('konten')
<div style="max-width: 1100px;" class="animasi-slide">
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Manajemen Seluruh Akun Pengguna (User CRUD)</h1>
            <p style="color: var(--abu-teks);">Kelola akun Super Admin, Admin, Asesor Penguji, dan Asesi (Peserta Uji)</p>
        </div>
        <div>
            <button class="tombol tombol-utama" onclick="bukaModal('modalTambahPengguna')">
                <i class="fa-solid fa-user-plus"></i> Tambah Akun Pengguna
            </button>
        </div>
    </div>

    <!-- FILTER PERAN -->
    <form action="{{ route('superadmin.manajemen-pengguna') }}" method="GET" style="margin-bottom: 1.5rem;" class="kartu">
        <div style="display: flex; gap: 1rem;">
            <input type="text" name="q" class="input-control" placeholder="Cari nama atau email..." value="{{ $kataKunci }}">
            <select name="peran" class="input-control" style="width: 200px;">
                <option value="">-- Semua Role --</option>
                <option value="superadmin" {{ $peranFilter == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                <option value="admin" {{ $peranFilter == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="asesor" {{ $peranFilter == 'asesor' ? 'selected' : '' }}>Asesor</option>
                <option value="asesi" {{ $peranFilter == 'asesi' ? 'selected' : '' }}>Asesi</option>
            </select>
            <button type="submit" class="tombol tombol-utama"><i class="fa-solid fa-search"></i> Cari</button>
        </div>
    </form>

    <div class="kartu">
        <div class="tabel-wadah">
            <table class="tabel-custom">
                <thead>
                    <tr>
                        <th>Nama Lengkap</th>
                        <th>Email Login</th>
                        <th>Peran (Role)</th>
                        <th>No. Telepon</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penggunaList as $user)
                        <tr>
                            <td><strong>{{ $user->nama_lengkap }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->peran === 'superadmin')
                                    <span class="lencana lencana-merah">Super Admin</span>
                                @elseif($user->peran === 'admin')
                                    <span class="lencana lencana-biru">Admin</span>
                                @elseif($user->peran === 'asesor')
                                    <span class="lencana lencana-hijau">Asesor</span>
                                @else
                                    <span class="lencana lencana-amber">Asesi</span>
                                @endif
                            </td>
                            <td>{{ $user->nomor_telepon ?? '-' }}</td>
                            <td>
                                @if($user->aktif)
                                    <span class="lencana lencana-hijau">Aktif</span>
                                @else
                                    <span class="lencana lencana-merah">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.35rem;">
                                    <button class="tombol tombol-sekunder tombol-sm" onclick="bukaModal('modalEditUser_{{ $user->id }}')">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('superadmin.pengguna.hapus', $user->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tombol tombol-bahaya tombol-sm"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL EDIT PENGGUNA -->
                        <div class="modal-overlay" id="modalEditUser_{{ $user->id }}">
                            <div class="modal-konten">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                                    <h3 style="color: var(--biru-malam);">Ubah Akun: {{ $user->nama_lengkap }}</h3>
                                    <button onclick="tutupModal('modalEditUser_{{ $user->id }}')" style="background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
                                </div>

                                <form action="{{ route('superadmin.pengguna.ubah', $user->id) }}" method="POST">
                                    @csrf
                                    <div class="grup-form">
                                        <label class="label-form">Nama Lengkap</label>
                                        <input type="text" name="nama_lengkap" class="input-control" value="{{ $user->nama_lengkap }}" required>
                                    </div>
                                    <div class="grup-form">
                                        <label class="label-form">Email</label>
                                        <input type="email" name="email" class="input-control" value="{{ $user->email }}" required>
                                    </div>
                                    <div class="grup-form">
                                        <label class="label-form">Hak Akses Role</label>
                                        <select name="peran" class="input-control" required>
                                            <option value="superadmin" {{ $user->peran == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                            <option value="admin" {{ $user->peran == 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="asesor" {{ $user->peran == 'asesor' ? 'selected' : '' }}>Asesor</option>
                                            <option value="asesi" {{ $user->peran == 'asesi' ? 'selected' : '' }}>Asesi</option>
                                        </select>
                                    </div>
                                    <div class="grup-form">
                                        <label class="label-form">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                                        <div class="input-sandi-wrapper">
                                            <input type="password" name="kata_sandi" class="input-control" placeholder="••••••••">
                                            <button type="button" class="tombol-intip-sandi" title="Tampilkan/Sembunyikan Kata Sandi">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="grup-form" style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" name="aktif" value="1" id="aktif_{{ $user->id }}" {{ $user->aktif ? 'checked' : '' }}>
                                        <label for="aktif_{{ $user->id }}" style="font-size: 0.9rem; font-weight: 600;">Status Akun Aktif</label>
                                    </div>
                                    <div style="margin-top: 1.5rem; text-align: right;">
                                        <button type="submit" class="tombol tombol-utama">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--abu-teks); padding: 2rem;">Tidak ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $penggunaList->links() }}
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PENGGUNA -->
<div class="modal-overlay" id="modalTambahPengguna">
    <div class="modal-konten">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="color: var(--biru-malam);">Buat Akun Pengguna Baru</h3>
            <button onclick="tutupModal('modalTambahPengguna')" style="background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('superadmin.pengguna.simpan') }}" method="POST">
            @csrf
            <div class="grup-form">
                <label class="label-form">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="input-control" required placeholder="Nama Pengguna">
            </div>

            <div class="grup-form">
                <label class="label-form">Email Resmi</label>
                <input type="email" name="email" class="input-control" required placeholder="email@lsp.id">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grup-form">
                    <label class="label-form">Role Hak Akses</label>
                    <select name="peran" class="input-control" required>
                        <option value="asesi">Asesi (Peserta)</option>
                        <option value="asesor">Asesor (Penguji)</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                <div class="grup-form">
                    <label class="label-form">No. Telepon / WA</label>
                    <input type="text" name="nomor_telepon" class="input-control" placeholder="0812...">
                </div>
            </div>

            <div class="grup-form">
                <label class="label-form">Kata Sandi</label>
                <div class="input-sandi-wrapper">
                    <input type="password" name="kata_sandi" class="input-control" required placeholder="Minimal 6 karakter">
                    <button type="button" class="tombol-intip-sandi" title="Tampilkan/Sembunyikan Kata Sandi">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div style="margin-top: 1.5rem; text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModal('modalTambahPengguna')">Batal</button>
                <button type="submit" class="tombol tombol-utama">Buat Akun Pengguna</button>
            </div>
        </form>
    </div>
</div>
@endsection
