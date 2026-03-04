@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Manajemen Sistem</h3>
    <a href="{{ route('admin.export') }}" class="btn btn-success shadow-sm">
        <i class="fas fa-file-export me-2"></i> Eksport Data Masyarakat (CSV)
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between">
                <h5 class="mb-0 fw-bold">Daftar Petugas</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPetugas">
                    + Tambah Petugas
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Loket</th>
                            <th>Layanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td><span class="badge bg-light text-dark">{{ $user->username }}</span></td>
                            <td>{{ $user->loket->nama_loket ?? '-' }}</td>
                            <td>{{ $user->layanan->nama_layanan ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Pengaturan Nama Loket</h5>
            </div>
            <div class="card-body">
                @foreach($lokets as $loket)
                <form action="{{ route('admin.loket.update', $loket->id) }}" method="POST" class="mb-3">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="nama_loket" class="form-control" value="{{ $loket->nama_loket }}">
                        <button class="btn btn-dark" type="submit">Update</button>
                    </div>
                </form>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahPetugas" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Akun Petugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.petugas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Petugas</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tugaskan di Loket</label>
                        <select name="loket_id" class="form-select" required>
                            @foreach($lokets as $l)
                                <option value="{{ $l->id }}">{{ $l->nama_loket }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pegang Layanan</label>
                        <select name="layanan_id" class="form-select" required>
                            @foreach($layanans as $lay)
                                <option value="{{ $lay->id }}">{{ $lay->nama_layanan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">SIMPAN PETUGAS</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection