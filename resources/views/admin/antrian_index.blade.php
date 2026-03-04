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

<div class="card border-0 shadow-sm mb-5">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
        <h5 class="fw-bold mb-0">Daftar Antrian</h5>
        {{-- Tombol Export CSV --}}
        <a href="{{ route('admin.export') }}" class="btn btn-success fw-bold rounded-pill px-4 shadow-sm">
            <i class="fas fa-file-csv me-2"></i> Export Data CSV
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No. Antrian</th>
                        <th>Nama Pendaftar</th>
                        <th>NIK</th>
                        <th>Layanan</th>
                        <th>Waktu Daftar</th>
                        <th>Petugas & Loket</th> {{-- Kolom Diperbarui --}}
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataAntrian as $q)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $q->nomor_antrian }}</td>
                        <td>{{ $q->nama_pendaftar }}</td>
                        <td><code>{{ $q->nik }}</code></td>
                        <td><span class="badge bg-light text-dark border">{{ $q->layanan->nama_layanan ?? '-' }}</span></td>
                        <td>
                            <div class="small">
                                <i class="far fa-calendar-alt me-1 text-muted"></i> {{ $q->created_at->translatedFormat('d M Y') }}<br>
                                <i class="far fa-clock me-1 text-muted"></i> {{ $q->created_at->format('H:i') }} WIB
                            </div>
                        </td>
                        <td>
                            @if($q->user_id) {{-- Cek jika sudah ada petugas yang memanggil --}}
                                <div class="d-flex flex-column">
                                    <span class="small fw-bold text-dark">
                                        <i class="fas fa-user-tie me-1 text-primary"></i> {{ $q->petugas->name ?? 'Petugas' }}
                                    </span>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle mt-1" style="width: fit-content; font-size: 0.75rem;">
                                        <i class="fas fa-desktop me-1"></i> {{ $q->loket->nama_loket ?? 'Loket' }}
                                    </span>
                                </div>
                            @else
                                <span class="badge bg-light text-muted fw-normal border">
                                    <i class="fas fa-hourglass-start me-1"></i> Menunggu...
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{-- Tombol Edit --}}
                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $q->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            {{-- Form Hapus --}}
                            <form action="{{ route('admin.antrian.delete', $q->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data antrian ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Modal Edit per baris --}}
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
                                            <input type="text" name="nama_pendaftar" class="form-control" value="{{ $q->nama_pendaftar }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">NIK</label>
                                            <input type="number" name="nik" class="form-control" value="{{ $q->nik }}" required>
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
@endsection