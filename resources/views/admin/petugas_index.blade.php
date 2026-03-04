@extends('layouts.app')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-0">Manajemen Petugas & Loket</h3>
        <p class="text-muted small">Atur penempatan petugas berdasarkan loket dan jenis layanan yang ditangani.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark shadow-sm rounded-pill">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Tabel Kelola Petugas --}}
    <div class="col-md-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-users-cog me-2 text-primary"></i>Daftar Petugas Aktif</h5>
                <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="fas fa-plus me-1"></i> Tambah Petugas
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Username & Nama</th>
                                <th>Loket</th>
                                <th>Layanan Spesifik</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($petugas as $p)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $p->name }}</div>
                                    <code class="small text-primary">{{ $p->username }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-soft-info text-info px-3 py-2 rounded-pill">
                                        <i class="fas fa-desktop me-1"></i> {{ $p->loket->nama_loket ?? 'Belum Diatur' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-soft-success text-success px-3 py-2 rounded-pill">
                                        <i class="fas fa-tag me-1"></i> {{ $p->layanan->nama_layanan ?? 'Belum Diatur' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-warning text-white px-3 rounded-pill" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEdit{{ $p->id }}">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>
                                    
                                    <form action="{{ route('admin.petugas.delete', $p->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-3 rounded-pill" onclick="return confirm('Hapus petugas ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Modal Edit Petugas (LENGKAP: Nama, Username, Pass, Loket, Layanan) --}}
                            <div class="modal fade" id="modalEdit{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-header border-0 bg-light">
                                            <h5 class="modal-title fw-bold">Update Profil & Akses</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.petugas.update', $p->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Nama Petugas</label>
                                                    <input type="text" name="name" class="form-control rounded-3" value="{{ $p->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Username</label>
                                                    <input type="text" name="username" class="form-control rounded-3" value="{{ $p->username }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-danger">Ganti Password (Kosongkan jika tidak)</label>
                                                    <input type="password" name="password" class="form-control rounded-3" placeholder="******">
                                                </div>
                                                <hr>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Edit Loket</label>
                                                        <select name="loket_id" class="form-select rounded-3" required>
                                                            @foreach($lokets as $l)
                                                            <option value="{{ $l->id }}" {{ $p->loket_id == $l->id ? 'selected' : '' }}>{{ $l->nama_loket }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Edit Layanan</label>
                                                        <select name="layanan_id" class="form-select rounded-3" required>
                                                            @foreach($layanans as $lay)
                                                            <option value="{{ $lay->id }}" {{ $p->layanan_id == $lay->id ? 'selected' : '' }}>{{ $lay->nama_layanan }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-pill shadow">SIMPAN PERUBAHAN</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">Belum ada data petugas aktif.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel Samping: Kelola Master Loket --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-door-open me-2 text-secondary"></i>Nama Loket Fisik</h6>
            </div>
            <div class="card-body">
                @foreach($lokets as $loket)
                <form action="{{ route('admin.loket.update', $loket->id) }}" method="POST" class="mb-2">
                    @csrf
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 small fw-bold">{{ $loket->id }}</span>
                        <input type="text" name="nama_loket" class="form-control form-control-sm" value="{{ $loket->nama_loket }}">
                        <button class="btn btn-sm btn-dark" type="submit"><i class="fas fa-save"></i></button>
                    </div>
                </form>
                @endforeach
                <small class="text-muted italic d-block mt-2">Ubah nama identitas loket di sini.</small>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Petugas --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white">
                <h5 class="modal-title fw-bold">Tambah Akun Petugas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.petugas.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="Contoh: Andi Wijaya" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" class="form-control rounded-3" placeholder="Untuk login petugas" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control rounded-3" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Penempatan Loket</label>
                            <select name="loket_id" class="form-select rounded-3" required>
                                <option value="" disabled selected>Pilih Loket...</option>
                                @foreach($lokets as $l)
                                <option value="{{ $l->id }}">{{ $l->nama_loket }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Fokus Layanan</label>
                            <select name="layanan_id" class="form-select rounded-3" required>
                                <option value="" disabled selected>Pilih Layanan...</option>
                                @foreach($layanans as $lay)
                                <option value="{{ $lay->id }}">{{ $lay->nama_layanan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow">DAFTARKAN PETUGAS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .form-control, .form-select { border-radius: 8px; padding: 0.6rem 1rem; }
    .italic { font-style: italic; font-size: 0.75rem; }
</style>
@endsection