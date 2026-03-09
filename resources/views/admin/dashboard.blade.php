@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <h2 class="fw-bold text-dark"><i class="fas fa-gauge-high me-2"></i> Dashboard Admin</h2>
        <p class="text-muted">Pantau seluruh aktivitas antrian Dukcapil di sini secara realtime.</p>
    </div>

    {{-- Ringkasan Statistik (5 Status Terpadu) --}}
    <div class="row g-4 mb-4">
        {{-- Total Antrian --}}
        <div class="col-md-2-5 col-sm-6">
            <div class="card bg-primary text-white border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75">Total Antrian</h6>
                            <h2 class="fw-bold mb-0" id="total-antrian-count">{{ $totalAntrian ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Menunggu --}}
        <div class="col-md-2-5 col-sm-6">
            <div class="card bg-secondary text-white border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75">Menunggu</h6>
                            <h2 class="fw-bold mb-0" id="menunggu-count">{{ $menunggu ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sedang Diproses --}}
        <div class="col-md-2-5 col-sm-6">
            <div class="card bg-info text-white border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75">Diproses</h6>
                            <h2 class="fw-bold mb-0" id="diproses-count">{{ $diproses ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-spinner fa-2x opacity-25 fa-spin-slow"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Selesai Dilayani --}}
        <div class="col-md-2-5 col-sm-6">
            <div class="card bg-success text-white border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75">Selesai</h6>
                            <h2 class="fw-bold mb-0" id="selesai-count">{{ $selesai ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-check-double fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pengambilan Dokumen --}}
        <div class="col-md-2-5 col-sm-6">
            <div class="card bg-warning text-white border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75">Pengambilan Dokumen</h6>
                            <h2 class="fw-bold mb-0" id="pengambilan-count">{{ $pengambilanDokumen ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-file-export fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris Kedua (Dilewati) --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75">Dilewati</h6>
                            <h3 class="fw-bold mb-0" id="lewat-count">{{ $lewat ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-times-circle fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pusat Manajemen --}}
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body text-center py-5">
            <h5 class="fw-bold mb-4 text-dark">Pusat Manajemen Sistem</h5>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('admin.layanan') }}" class="btn btn-outline-success px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-concierge-bell me-2"></i> Kelola Layanan
                </a>
                <a href="{{ route('admin.petugas') }}" class="btn btn-outline-primary px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-user-cog me-2"></i> Kelola Petugas
                </a>
                <a href="{{ route('admin.antrian') }}" class="btn btn-outline-info px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-users-viewfinder me-2"></i> Kelola Antrian
                </a>
                <a href="{{ route('display') }}" target="_blank" class="btn btn-outline-dark px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-external-link-alt me-2"></i> Cek Display
                </a>
                <a href="{{ route('admin.reset-display') }}" onclick="return confirm('PERHATIAN: Ini akan menghapus seluruh data antrian hari ini. Lanjutkan?')" class="btn btn-outline-danger px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-rotate me-2"></i> Reset Sistem (Mulai 0)
                </a>
            </div>
        </div>
    </div>

    {{-- Tabel Monitoring Realtime --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ol me-2 text-primary"></i> Monitoring Antrian Hari Ini</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">No. Antrian</th>
                        <th>Nama Pendaftar</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Petugas & Loket</th>
                        <th class="text-end pe-4">Waktu</th>
                    </tr>
                </thead>
                <tbody id="antrian-table-body">
                    @forelse($dataAntrian as $item)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $item->nomor_antrian }}</td>
                        <td>{{ $item->nama_pendaftar }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $item->layanan->nama_layanan ?? '-' }}</span></td>
                        <td>
                            @php
                                $status = $item->status;
                                // Logika penanganan status 'diproses' jika petugas sudah mengambil namun status DB masih menunggu
                                if ($status == 'menunggu' && !empty($item->user_id)) {
                                    $status = 'diproses';
                                }
                            @endphp
                            
                            @if($status == 'menunggu')
                                <span class="badge bg-secondary">Menunggu</span>
                            @elseif($status == 'diproses' || $status == 'dipanggil')
                                <span class="badge bg-info text-dark">Diproses</span>
                            @elseif($status == 'selesai')
                                <span class="badge bg-success">Selesai</span>
                            @elseif($status == 'pengambilan')
                                <span class="badge bg-warning text-dark">Pengambilan Dokumen</span>
                            @elseif($status == 'lewat')
                                <span class="badge bg-danger">Dilewati</span>
                            @endif
                        </td>
                        <td>
                            @if($item->petugas)
                                <div class="small fw-bold text-dark"><i class="fas fa-user-tie me-1"></i> {{ $item->petugas->name }}</div>
                                <div class="small text-muted"><i class="fas fa-desktop me-1"></i> {{ $item->loket->nama_loket ?? '-' }}</div>
                            @else
                                <span class="text-muted small">Belum dipanggil</span>
                            @endif
                        </td>
                        <td class="text-end pe-4 small text-muted">
                            {{ $item->created_at->timezone('Asia/Jakarta')->format('H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada antrian hari ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Script Realtime Update --}}
    <script>
        function fetchRealtimeStats() {
            fetch("{{ route('admin.realtime-stats') }}")
                .then(response => response.json())
                .then(data => {
                    // Update Widget Card
                    document.getElementById('total-antrian-count').innerText = data.totalAntrian;
                    document.getElementById('menunggu-count').innerText = data.menunggu;
                    document.getElementById('diproses-count').innerText = data.diproses;
                    document.getElementById('selesai-count').innerText = data.selesai;
                    document.getElementById('lewat-count').innerText = data.lewat;
                    // ID disesuaikan menjadi pengambilan-count
                    document.getElementById('pengambilan-count').innerText = data.pengambilanDokumen || 0;
                })
                .catch(error => console.error('Error fetching stats:', error));
        }

        // Jalankan setiap 3 detik
        setInterval(fetchRealtimeStats, 3000);
    </script>

    <style>
        /* Custom Grid untuk 5 Kolom Statistik */
        .col-md-2-5 { width: 20%; flex: 0 0 20%; padding: 0 10px; }
        @media (max-width: 992px) { .col-md-2-5 { width: 33.33%; flex: 0 0 33.33%; } }
        @media (max-width: 768px) { .col-md-2-5 { width: 50%; flex: 0 0 50%; } }
        @media (max-width: 576px) { .col-md-2-5 { width: 100%; flex: 0 0 100%; } }
        
        .fa-spin-slow { animation: fa-spin 3s infinite linear; }
    </style>
@endsection