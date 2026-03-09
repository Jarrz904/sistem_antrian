@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        {{-- PANEL KONTROL PETUGAS --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-desktop me-2"></i> Area Kerja Petugas</h5>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill mb-2">IDENTITAS LOKET</span>
                        <h2 class="fw-800 text-dark mb-0">{{ auth()->user()->loket->nama_loket ?? 'Loket Umum' }}</h2>
                        <p class="text-primary fw-bold mb-0">{{ auth()->user()->layanan->nama_layanan ?? 'Semua Layanan' }}</p>
                        <p class="text-muted small">Petugas: {{ auth()->user()->name }}</p>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div id="status-pelayanan">
                        @if($current)
                            {{-- KONDISI 1: JIKA ADA ANTRIAN YANG SEDANG DILAYANI --}}
                            <p class="small text-uppercase fw-bold text-muted mb-2">Antrian Sedang Dilayani</p>
                            <div class="p-4 bg-light rounded-4 mb-4 border border-primary shadow-sm" style="position: relative; overflow: hidden;">
                                <div class="spinner-grow spinner-grow-sm text-primary" role="status" style="position: absolute; top: 15px; right: 15px;"></div>
                                
                                <h1 class="display-1 fw-800 text-primary mb-0">{{ $current->nomor_antrian }}</h1>
                                <p class="fw-bold text-dark mb-0 mt-2">{{ $current->layanan->nama_layanan }}</p>
                                <small class="text-muted">An. {{ $current->nama_pendaftar }}</small>
                            </div>

                            <div class="d-grid gap-3">
                                {{-- Tombol 1: Selesai (Mengubah status jadi selesai tanpa panggil baru) --}}
                                <form action="{{ route('petugas.aksi', ['id' => $current->id, 'status' => 'selesai']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm border-0">
                                        <i class="fas fa-check-circle me-2"></i> SELESAI
                                    </button>
                                </form>

                                {{-- Tombol 2: Nomor Berikutnya (Selesai & Panggil yang baru) --}}
                                <form action="{{ route('petugas.panggil') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-lg py-3 border-0" style="background: linear-gradient(45deg, #0d6efd, #0043a8);">
                                        NOMOR BERIKUTNYA <i class="fas fa-chevron-right ms-2"></i>
                                    </button>
                                </form>
                                
                                {{-- Tombol 3: Lewati Nomor --}}
                                <form action="{{ route('petugas.aksi', ['id' => $current->id, 'status' => 'lewat']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-100 fw-bold py-3">
                                        LEWATI NOMOR INI <i class="fas fa-step-forward ms-1"></i>
                                    </button>
                                </form>
                            </div>

                        @elseif($antrian->count() > 0)
                            {{-- KONDISI 2: JIKA TIDAK ADA YANG DILAYANI TAPI ADA DAFTAR TUNGGU --}}
                            <div class="py-4">
                                <div class="mb-3">
                                    <i class="fas fa-user-clock fa-4x text-primary opacity-25"></i>
                                </div>
                                <h5 class="fw-bold">Antrian Tersedia</h5>
                                <p class="text-muted small">Ada <strong>{{ $antrian->count() }}</strong> orang menunggu. Silakan panggil antrian.</p>
                            </div>
                            
                            <form action="{{ route('petugas.panggil') }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-lg py-4 border-0" style="background: linear-gradient(45deg, #0d6efd, #0043a8);">
                                    PANGGIL ANTRIAN <i class="fas fa-volume-up ms-2"></i>
                                </button>
                            </form>
                        @else
                            {{-- KONDISI 3: JIKA BENAR-BENAR KOSONG --}}
                            <div class="py-4">
                                <div class="mb-3">
                                    <i class="fas fa-mug-hot fa-4x text-light"></i>
                                </div>
                                <h5 class="fw-bold text-muted">Belum Ada Antrian</h5>
                                <p class="text-muted small">Daftar tunggu kosong untuk saat ini.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- DAFTAR ANTRIAN --}}
        <div class="col-md-8">
            {{-- Tabel Menunggu --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-list-ol me-2"></i> Daftar Tunggu (Hari Ini)</h5>
                    <span id="count-menunggu" class="badge bg-danger rounded-pill px-3">{{ $antrian->count() }} Orang</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" width="150">No. Antrian</th>
                                    <th>Nama Pendaftar</th>
                                    <th>NIK</th>
                                    <th class="text-center">Jam Daftar</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-menunggu">
                                @forelse($antrian as $a)
                                <tr>
                                    <td class="ps-4"><span class="fw-bold text-primary h5 mb-0">{{ $a->nomor_antrian }}</span></td>
                                    <td class="fw-bold">{{ $a->nama_pendaftar }}</td>
                                    <td><small class="text-muted">{{ $a->nik ?? '---' }}</small></td>
                                    <td class="text-center small text-muted">{{ $a->created_at->format('H:i') }}</td>
                                </tr>
                                @empty
                                <tr class="empty-row">
                                    <td colspan="4" class="text-center py-5">
                                        <p class="text-muted mb-0">Belum ada antrian baru hari ini.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tabel Dilewati --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-secondary"><i class="fas fa-history me-2"></i> Baru Saja Dilewati</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" width="150">No. Antrian</th>
                                    <th>Nama Pendaftar</th>
                                    <th class="text-end pe-4">Aksi Balas</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-skipped">
                                @forelse($skipped as $s)
                                <tr>
                                    <td class="ps-4"><span class="fw-bold text-danger">{{ $s->nomor_antrian }}</span></td>
                                    <td>
                                        <div class="fw-bold">{{ $s->nama_pendaftar }}</div>
                                        <small class="text-muted" style="font-size: 10px;">Jam: {{ $s->updated_at->format('H:i') }}</small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('petugas.panggilUlang', $s->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning fw-bold px-3 shadow-sm rounded-pill">
                                                RECALL <i class="fas fa-redo ms-1"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted small">Tidak ada nomor yang dilewati.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script Realtime AJAX Polling --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function fetchAntrianRealtime() {
        $.ajax({
            url: "{{ route('petugas.dashboard') }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                // Catatan: Jika Anda menggunakan AJAX, pastikan response JSON juga 
                // mengirimkan data 'current' agar tombol bisa update otomatis tanpa refresh.
                
                $('#count-menunggu').text(response.count + ' Orang');

                // Update Tabel Menunggu
                let htmlMenunggu = '';
                if (response.antrian.length > 0) {
                    response.antrian.forEach(function(a) {
                        htmlMenunggu += `
                            <tr>
                                <td class="ps-4"><span class="fw-bold text-primary h5 mb-0">${a.nomor_antrian}</span></td>
                                <td class="fw-bold">${a.nama_pendaftar}</td>
                                <td><small class="text-muted">${a.nik ?? '---'}</small></td>
                                <td class="text-center small text-muted">Baru saja</td>
                            </tr>`;
                    });
                } else {
                    htmlMenunggu = '<tr><td colspan="4" class="text-center py-5">Belum ada antrian baru.</td></tr>';
                }
                $('#tbody-menunggu').html(htmlMenunggu);

                // Update Tabel Dilewati
                let htmlSkipped = '';
                if (response.skipped.length > 0) {
                    response.skipped.forEach(function(s) {
                        let recallUrl = "{{ route('petugas.panggilUlang', ':id') }}".replace(':id', s.id);
                        
                        htmlSkipped += `
                            <tr>
                                <td class="ps-4"><span class="fw-bold text-danger">${s.nomor_antrian}</span></td>
                                <td><div class="fw-bold">${s.nama_pendaftar}</div></td>
                                <td class="text-end pe-4">
                                    <form action="${recallUrl}" method="POST" class="d-inline">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-sm btn-warning fw-bold px-3 shadow-sm rounded-pill">
                                            RECALL <i class="fas fa-redo ms-1"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>`;
                    });
                } else {
                    htmlSkipped = '<tr><td colspan="3" class="text-center py-4 text-muted small">Tidak ada nomor yang dilewati.</td></tr>';
                }
                $('#tbody-skipped').html(htmlSkipped);
            }
        });
    }

    $(document).ready(function() {
        setInterval(fetchAntrianRealtime, 3000);
    });
</script>

<style>
    .fw-800 { font-weight: 800; }
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .rounded-4 { border-radius: 1.5rem !important; }
    .table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection