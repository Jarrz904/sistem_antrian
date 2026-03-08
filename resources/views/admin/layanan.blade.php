@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            {{-- Fitur Kembali ke Dashboard --}}
            <a href="{{ route('admin.dashboard') }}" class="btn btn-white rounded-circle shadow-sm me-3 hover-lift d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;" title="Kembali ke Dashboard">
                <i class="fas fa-arrow-left text-primary"></i>
            </a>
            <div>
                <h2 class="fw-bold text-dark mb-1">
                    <i class="fas fa-concierge-bell text-primary me-2"></i> Manajemen Layanan
                </h2>
                <p class="text-muted mb-0">Kelola daftar layanan dan pengaturan nomor antrian (Prefix).</p>
            </div>
        </div>
        <button type="button" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm hover-lift" data-bs-toggle="modal" data-bs-target="#modalTambahLayanan">
            <i class="fas fa-plus-circle me-2"></i> Tambah Layanan
        </button>
    </div>

    {{-- Alert Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-3"></i>
                <div><strong>Berhasil!</strong> {{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fs-4 me-3"></i>
                <div><strong>Gagal!</strong> {{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Data Table Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase tracking-wider" width="120">Prefix</th>
                            <th class="py-3 text-uppercase tracking-wider">Informasi Layanan</th>
                            <th class="py-3 text-uppercase tracking-wider text-center">Persyaratan</th>
                            <th class="py-3 text-uppercase tracking-wider text-center">Status</th>
                            <th class="pe-4 py-3 text-uppercase tracking-wider text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($layanan as $l)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-prefix bg-primary text-white fw-bold shadow-sm">
                                        {{ $l->prefix }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $l->nama_layanan }}</div>
                                <div class="text-muted small text-truncate" style="max-width: 300px;">
                                    {{ $l->deskripsi ?? 'Tidak ada deskripsi tambahan.' }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($l->is_nik_required)
                                    <span class="badge rounded-pill bg-soft-warning text-warning border border-warning px-3">
                                        <i class="fas fa-id-card me-1"></i> Wajib NIK
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-soft-info text-info border border-info px-3">
                                        <i class="fas fa-user-slash me-1"></i> Tanpa NIK
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success rounded-pill px-3 py-2 shadow-sm" style="font-size: 10px;">
                                    <i class="fas fa-check me-1"></i> AKTIF
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                    <button class="btn btn-white btn-sm px-3 border-end" 
                                            onclick="editLayanan({{ $l->id }}, '{{ $l->nama_layanan }}', '{{ $l->prefix }}', {{ $l->is_nik_required ? 'true' : 'false' }}, '{{ $l->deskripsi }}')"
                                            title="Edit Data">
                                        <i class="fas fa-edit text-info"></i>
                                    </button>
                                    <form action="{{ route('admin.layanan.destroy', $l->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-white btn-sm px-3" 
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')"
                                                title="Hapus Layanan">
                                            <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-folder-open fa-4x text-light mb-3"></i>
                                    <h5 class="text-muted fw-normal">Belum ada data layanan</h5>
                                    <button class="btn btn-sm btn-primary rounded-pill mt-2" data-bs-toggle="modal" data-bs-target="#modalTambahLayanan">
                                        Buat Layanan Pertama
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambahLayanan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark"><i class="fas fa-plus-circle text-primary me-2"></i>Tambah Layanan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.layanan.store') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">NAMA LAYANAN</label>
                        <input type="text" name="nama_layanan" class="form-control form-control-lg rounded-3 fs-6" placeholder="Contoh: Perekaman KTP-el" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">KODE PREFIX</label>
                            <input type="text" name="kode_layanan" class="form-control form-control-lg rounded-3 text-center fw-bold text-primary" placeholder="A" maxlength="1" required style="text-transform: uppercase;">
                            <div class="form-text mt-1" style="font-size: 10px;">Gunakan 1 Huruf (A-Z)</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">VALIDASI NIK</label>
                            <select name="is_nik_required" class="form-select form-select-lg rounded-3 fs-6">
                                <option value="1">Wajib NIK</option>
                                <option value="0">Tanpa NIK</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">DESKRIPSI / KETERANGAN</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="3" placeholder="Jelaskan singkat mengenai layanan ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEditLayanan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark"><i class="fas fa-edit text-info me-2"></i>Edit Informasi Layanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditLayanan" method="POST">
                @csrf @method('PUT')
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">NAMA LAYANAN</label>
                        <input type="text" name="nama_layanan" id="edit_nama" class="form-control form-control-lg rounded-3 fs-6" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">KODE PREFIX</label>
                            <input type="text" name="kode_layanan" id="edit_kode" class="form-control form-control-lg rounded-3 text-center fw-bold text-info" maxlength="1" required style="text-transform: uppercase;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">VALIDASI NIK</label>
                            <select name="is_nik_required" id="edit_nik" class="form-select form-select-lg rounded-3 fs-6">
                                <option value="1">Wajib NIK</option>
                                <option value="0">Tanpa NIK</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">DESKRIPSI / KETERANGAN</label>
                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control rounded-3" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-5 fw-bold shadow">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editLayanan(id, nama, prefix, isNikRequired, deskripsi) {
        let url = "{{ route('admin.layanan.update', ':id') }}".replace(':id', id);
        
        document.getElementById('formEditLayanan').action = url;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_kode').value = prefix;
        document.getElementById('edit_nik').value = isNikRequired ? "1" : "0";
        document.getElementById('edit_deskripsi').value = (deskripsi && deskripsi !== 'null') ? deskripsi : '';
        
        new bootstrap.Modal(document.getElementById('modalEditLayanan')).show();
    }
</script>

<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #8392a5;
    }

    .tracking-wider { letter-spacing: 0.05em; font-size: 0.75rem; font-weight: 700; color: #64748b; }
    
    .avatar-prefix {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.2rem;
    }

    .bg-soft-warning { background-color: #fffbeb; }
    .bg-soft-info { background-color: #f0f9ff; }
    
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .btn-white {
        background: #fff;
        border: 1px solid #e2e8f0;
    }
    .btn-white:hover { background: #f8fafc; }

    .modal-content { border: none; }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
    }
</style>
@endsection