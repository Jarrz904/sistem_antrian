@extends('layouts.app')

@section('content')
    {{-- Header Section --}}
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark"><i class="fas fa-users-viewfinder me-2"></i> Kelola Antrian Masyarakat</h2>
            <p class="text-muted">Daftar riwayat antrian dan pengaturan data pendaftar.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-light border rounded-pill px-4 shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
        </a>
    </div>

    {{-- Panel Filter Otomatis --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form id="filterForm" action="{{ route('admin.antrian') }}" method="GET" class="row g-3">
                {{-- Filter Prefix --}}
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">Prefix</label>
                    <select name="prefix" class="form-select border-0 bg-light" onchange="this.form.submit()">
                        <option value="">Semua Huruf</option>
                        @foreach($prefixes as $char)
                            <option value="{{ $char }}" {{ request('prefix') == $char ? 'selected' : '' }}>
                                {{ $char }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Layanan --}}
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">Layanan</label>
                    <select name="layanan_id" class="form-select border-0 bg-light" onchange="this.form.submit()">
                        <option value="">Semua Layanan</option>
                        @foreach($layanans as $lay)
                            <option value="{{ $lay->id }}" {{ request('layanan_id') == $lay->id ? 'selected' : '' }}>
                                {{ $lay->nama_layanan }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status --}}
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                    <select name="status" class="form-select border-0 bg-light" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="dipanggil" {{ request('status') == 'dipanggil' ? 'selected' : '' }}>Dipanggil</option>
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses (Ke Loket Pengambilan)</option>
                        <option value="pengambilan_dokumen" {{ request('status') == 'pengambilan_dokumen' ? 'selected' : '' }}>Sedang Di Loket Pengambilan</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai (Arsip)</option>
                        <option value="lewat" {{ request('status') == 'lewat' ? 'selected' : '' }}>Dilewati</option>
                    </select>
                </div>

                {{-- Filter Rentang Tanggal --}}
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted text-uppercase">Rentang Tanggal Daftar</label>
                    <div class="input-group">
                        <input type="date" name="tgl_mulai" class="form-control border-0 bg-light"
                            value="{{ request('tgl_mulai') }}" onchange="this.form.submit()">
                        <span class="input-group-text border-0 bg-light">s/d</span>
                        <input type="date" name="tgl_selesai" class="form-control border-0 bg-light"
                            value="{{ request('tgl_selesai') }}" onchange="this.form.submit()">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
            <h5 class="fw-bold mb-0">Daftar Antrian</h5>
            <a href="{{ route('admin.export', request()->query()) }}"
                class="btn btn-success fw-bold rounded-pill px-4 shadow-sm">
                <i class="fas fa-file-csv me-2"></i> Export Data CSV
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th class="ps-4">No. Antrian</th>
                            <th>Waktu Daftar</th>
                            <th>Nama Pendaftar</th>
                            <th>NIK</th>
                            <th>Layanan</th>
                            <th>Status Terkini</th>
                            <th>Petugas & Loket</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="antrianTableBody">
                        @forelse($dataAntrian as $q)
                            <tr id="row-{{ $q->id }}">
                                <td class="ps-4 fw-bold text-primary">{{ $q->nomor_antrian }}</td>
                                <td class="text-nowrap">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">{{ $q->created_at->format('H:i:s') }}</span>
                                        <span class="text-muted small">{{ $q->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </td>
                                <td class="text-nowrap">{{ $q->nama_pendaftar }}</td>
                                <td><code>{{ $q->nik ?? '-' }}</code></td>
                                <td><span class="badge bg-light text-dark border">{{ $q->layanan->nama_layanan ?? '-' }}</span></td>
                                <td>
                                    @if($q->status == 'menunggu')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Menunggu</span>
                                    @elseif($q->status == 'dipanggil')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                                            <i class="fas fa-volume-up me-1"></i> Dipanggil
                                        </span>
                                    @elseif($q->status == 'diproses')
                                        <span class="badge bg-primary text-white border shadow-sm">
                                            <i class="fas fa-spinner fa-spin me-1"></i> Diproses (Ke Pengambilan)
                                        </span>
                                    @elseif($q->status == 'pengambilan_dokumen')
                                        <span class="badge bg-warning text-dark border border-warning shadow-sm">
                                            <i class="fas fa-file-signature me-1"></i> Di Loket Pengambilan
                                        </span>
                                    @elseif($q->status == 'selesai')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Selesai (Arsip)</span>
                                    @elseif($q->status == 'lewat')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Dilewati</span>
                                    @else
                                        <span class="badge bg-light text-dark border">{{ $q->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($q->user_id)
                                        <div class="d-flex flex-column">
                                            <span class="small fw-bold text-dark">
                                                <i class="fas fa-user-tie me-1 text-primary"></i>
                                                {{ $q->petugas->name ?? 'Petugas' }}
                                            </span>
                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                <i class="fas fa-desktop me-1"></i> {{ $q->loket->nama_loket ?? 'Loket' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted small italic">Belum dipanggil</span>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                        data-bs-target="#modalEdit{{ $q->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.antrian.delete', $q->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Hapus data antrian ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Modal Edit (Tetap Sama) --}}
                            <div class="modal fade" id="modalEdit{{ $q->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold">Update Antrian {{ $q->nomor_antrian }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.antrian.update', $q->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-uppercase">Nama Lengkap</label>
                                                    <input type="text" name="nama_pendaftar" class="form-control"
                                                        value="{{ $q->nama_pendaftar }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-uppercase">NIK</label>
                                                    <input type="number" name="nik" class="form-control" value="{{ $q->nik }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-uppercase text-primary">Status Antrian</label>
                                                    <select name="status" class="form-select border-primary">
                                                        <option value="menunggu" {{ $q->status == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                                        <option value="dipanggil" {{ $q->status == 'dipanggil' ? 'selected' : '' }}>Dipanggil</option>
                                                        <option value="diproses" {{ $q->status == 'diproses' ? 'selected' : '' }}>Diproses (Menuju Loket Pengambilan)</option>
                                                        <option value="pengambilan_dokumen" {{ $q->status == 'pengambilan_dokumen' ? 'selected' : '' }}>Sedang di Loket Pengambilan</option>
                                                        <option value="selesai" {{ $q->status == 'selesai' ? 'selected' : '' }}>Selesai (Arsip)</option>
                                                        <option value="lewat" {{ $q->status == 'lewat' ? 'selected' : '' }}>Dilewati</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-uppercase">Jenis Layanan</label>
                                                    <select name="layanan_id" class="form-select">
                                                        @foreach($layanans as $lay)
                                                            <option value="{{ $lay->id }}" {{ $q->layanan_id == $lay->id ? 'selected' : '' }}>
                                                                {{ $lay->nama_layanan }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">SIMPAN PERUBAHAN</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open d-block mb-2 fa-2x"></i>
                                    Data antrian tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function refreshTable() {
            // Jangan refresh jika modal sedang terbuka agar input user tidak hilang
            if ($('.modal.show').length > 0) return;

            let currentUrl = window.location.href;

            $.ajax({
                url: currentUrl,
                type: 'GET',
                dataType: 'html',
                success: function(response) {
                    let newTableBody = $(response).find('#antrianTableBody').html();
                    $('#antrianTableBody').html(newTableBody);
                },
                error: function(xhr) {
                    console.error("Gagal memperbarui data antrian.");
                }
            });
        }

        $(document).ready(function() {
            // Refresh otomatis setiap 5 detik untuk memantau pendaftar baru
            setInterval(refreshTable, 5000);
        });
    </script>

    <style>
        .badge { font-weight: 600; padding: 0.5em 0.8em; }
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,.02); transition: 0.2s; }
        .modal-content { border-radius: 15px; }
        .form-select, .form-control { border-radius: 8px; }
        code { color: #d63384; word-wrap: break-word; }
    </style>
@endsection