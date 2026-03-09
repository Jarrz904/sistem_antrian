@extends('layouts.app')

@section('content')
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
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses (Aktif)</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="pengambilan" {{ request('status') == 'pengambilan' ? 'selected' : '' }}>Pengambilan Dokumen</option>
                        <option value="lewat" {{ request('status') == 'lewat' ? 'selected' : '' }}>Dilewati</option>
                    </select>
                </div>

                {{-- Filter Rentang Tanggal --}}
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted text-uppercase">Waktu Daftar</label>
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
                            <th>Nama Pendaftar</th>
                            <th>NIK</th>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th>Petugas & Loket</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="antrianTableBody">
                        @forelse($dataAntrian as $q)
                            <tr id="row-{{ $q->id }}">
                                <td class="ps-4 fw-bold text-primary">{{ $q->nomor_antrian }}</td>
                                <td class="text-nowrap">{{ $q->nama_pendaftar }}</td>
                                <td><code>{{ $q->nik ?? '-' }}</code></td>
                                <td><span class="badge bg-light text-dark border">{{ $q->layanan->nama_layanan ?? '-' }}</span></td>
                                <td>
                                    {{-- Logika 5 Status Terpadu --}}
                                    @if($q->status == 'selesai')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Selesai</span>
                                    @elseif($q->status == 'pengambilan')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                            <i class="fas fa-file-export me-1"></i> Pengambilan Dokumen
                                        </span>
                                    @elseif($q->status == 'lewat')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Dilewati</span>
                                    {{-- Status Diproses --}}
                                    @elseif(in_array($q->status, ['diproses', 'dipanggil']) || ($q->status == 'menunggu' && !empty($q->user_id)))
                                        <span class="badge bg-primary text-white border shadow-sm">
                                            <i class="fas fa-spinner fa-spin me-1"></i> Diproses
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Menunggu</span>
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

                            {{-- Modal Edit --}}
                            <div class="modal fade" id="modalEdit{{ $q->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Data Pendaftar</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.antrian.update', $q->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Nama Lengkap</label>
                                                    <input type="text" name="nama_pendaftar" class="form-control"
                                                        value="{{ $q->nama_pendaftar }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">NIK</label>
                                                    <input type="number" name="nik" class="form-control" value="{{ $q->nik }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Status Antrian</label>
                                                    <padding-left-0>
                                                    <select name="status" class="form-select">
                                                        <option value="menunggu" {{ $q->status == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                                        <option value="diproses" {{ in_array($q->status, ['diproses', 'dipanggil']) ? 'selected' : '' }}>Diproses</option>
                                                        <option value="pengambilan" {{ $q->status == 'pengambilan' ? 'selected' : '' }}>Pengambilan Dokumen</option>
                                                        <option value="selesai" {{ $q->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                        <option value="lewat" {{ $q->status == 'lewat' ? 'selected' : '' }}>Dilewati</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Jenis Layanan</label>
                                                    <select name="layanan_id" class="form-select">
                                                        @foreach($layanans as $lay)
                                                            <option value="{{ $lay->id }}" {{ $q->layanan_id == $lay->id ? 'selected' : '' }}>{{ $lay->nama_layanan }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="submit" class="btn btn-primary w-100 fw-bold">SIMPAN PERUBAHAN</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Data antrian tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function refreshTable() {
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
                    console.error("Gagal memperbarui data antrian secara otomatis.");
                }
            });
        }

        $(document).ready(function() {
            setInterval(refreshTable, 5000);
        });
    </script>
@endsection