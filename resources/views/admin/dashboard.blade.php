@extends('layouts.app')

@section('content')
    {{-- Tambahkan SweetAlert2 untuk notifikasi --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="mb-4">
        <h2 class="fw-bold text-dark"><i class="fas fa-gauge-high me-2"></i> Dashboard Admin</h2>
        <p class="text-muted">Pantau seluruh aktivitas antrian Dukcapil di sini secara realtime.</p>
    </div>

    {{-- Ringkasan Statistik (6 Status Utama) --}}
    <div class="row g-3 mb-4">
        {{-- Total Antrian --}}
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card bg-primary text-white border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75">Total</h6>
                            <h2 class="fw-bold mb-0" id="total-antrian-count">{{ $totalAntrian ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Menunggu --}}
        <div class="col-xl-2 col-md-4 col-sm-6">
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

        {{-- Dipanggil --}}
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card bg-info text-white border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75">Dipanggil</h6>
                            <h2 class="fw-bold mb-0" id="dipanggil-count">{{ $dipanggil ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-volume-up fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Selesai di Proses --}}
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card bg-dark text-white border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75">Selesai Diproses</h6>
                            <h2 class="fw-bold mb-0" id="diproses-count">{{ $selesaidiproses ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-spinner fa-2x opacity-25 fa-spin-slow"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pengambilan Dokumen --}}
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card bg-warning text-white border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75">Ambil Dokumen</h6>
                            <h2 class="fw-bold mb-0" id="pengambilan-count">{{ $pengambilanDokumen ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-file-export fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="col-xl-2 col-md-4 col-sm-6">
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
    </div>

    {{-- Status Tambahan: Dilewati --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-danger border-4 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small fw-bold text-danger">Total Dilewati</h6>
                            <h3 class="fw-bold mb-0 text-dark" id="dilewati-count">{{ $dilewati ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-user-slash fa-2x text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pusat Manajemen --}}
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body text-center py-5">
            <h5 class="fw-bold mb-4 text-dark">Pusat Manajemen Sistem</h5>
            
            <div id="status-system-container" class="mb-4">
                @if(($systemStatus ?? 'on') === 'off')
                    <div class="alert alert-danger d-inline-block px-5 rounded-pill animate-pulse">
                        <i class="fas fa-exclamation-triangle me-2"></i> <strong>SISTEM SEDANG DIHENTIKAN</strong>
                    </div>
                @else
                    <div class="alert alert-success d-inline-block px-5 rounded-pill">
                        <i class="fas fa-check-circle me-2"></i> <strong>SISTEM BERJALAN NORMAL</strong>
                    </div>
                @endif
            </div>

            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('admin.layanan') }}"
                    class="btn btn-outline-success px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-concierge-bell me-2"></i> Kelola Layanan
                </a>
                <a href="{{ route('admin.petugas') }}"
                    class="btn btn-outline-primary px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-user-cog me-2"></i> Kelola Petugas
                </a>
                <a href="{{ route('admin.antrian') }}"
                    class="btn btn-outline-info px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-users-viewfinder me-2"></i> Kelola Antrian
                </a>
                <a href="{{ route('display') }}" target="_blank"
                    class="btn btn-outline-dark px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-external-link-alt me-2"></i> Cek Display
                </a>

                {{-- TOMBOL STOP/START --}}
                <form action="{{ route('admin.toggle-status') }}" method="POST" class="d-inline" id="toggle-form">
                    @csrf
                    <input type="hidden" name="status" id="status-input" value="{{ ($systemStatus ?? 'on') === 'on' ? 'off' : 'on' }}">
                    <button type="submit" id="btn-toggle" class="btn {{ ($systemStatus ?? 'on') === 'on' ? 'btn-danger' : 'btn-success' }} px-4 py-2 fw-bold rounded-pill shadow-sm">
                        @if(($systemStatus ?? 'on') === 'on')
                            <i class="fas fa-stop-circle me-2"></i> Hentikan Antrian
                        @else
                            <i class="fas fa-play me-2"></i> Aktifkan Antrian
                        @endif
                    </button>
                </form>

                <a href="{{ route('admin.reset-display') }}"
                    onclick="return confirm('PERHATIAN: Ini akan menghapus seluruh data antrian hari ini. Lanjutkan?')"
                    class="btn btn-outline-danger px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-rotate me-2"></i> Reset Sistem
                </a>
            </div>
        </div>
    </div>

    {{-- Tabel Monitoring Realtime --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ol me-2 text-primary"></i> Monitoring Antrian Hari Ini</h5>
            <span class="badge bg-light text-primary border"><i class="fas fa-sync fa-spin me-1"></i> Realtime Update</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">No. Antrian</th>
                        <th>Nama Pendaftar</th>
                        <th>Layanan</th>
                        <th>Status Saat Ini</th>
                        <th>Lokasi/Loket Terakhir</th>
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
                                    $badgeClass = 'bg-secondary';
                                    $displayLabel = strtoupper(str_replace('_', ' ', $status));
                                    
                                    if ($status == 'menunggu') $badgeClass = 'bg-secondary';
                                    elseif ($status == 'dipanggil') $badgeClass = 'bg-info text-white animate-pulse';
                                    elseif ($status == 'selesai diproses') $badgeClass = 'bg-primary';
                                    elseif ($status == 'pengambilan_dokumen') $badgeClass = 'bg-warning text-dark border border-warning';
                                    elseif ($status == 'selesai') $badgeClass = 'bg-success';
                                    elseif ($status == 'dilewati' || $status == 'lewat') {
                                        $badgeClass = 'bg-danger';
                                        $displayLabel = 'DILEWATI';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ $displayLabel }}
                                </span>
                            </td>
                            <td>
                                @if($item->user_id)
                                    <div class="small fw-bold text-dark"><i class="fas fa-desktop me-1"></i> {{ $item->loket->nama_loket ?? 'Loket' }}</div>
                                    <div class="small text-muted"><i class="fas fa-user-tie me-1"></i> {{ $item->petugas->name ?? '-' }}</div>
                                @else
                                    <span class="text-muted small italic">Belum dipanggil</span>
                                @endif
                            </td>
                            <td class="text-end pe-4 small text-muted">{{ $item->created_at->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada antrian hari ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Script Realtime Update --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            function fetchRealtimeData() {
                fetch("{{ route('admin.realtime-stats') }}")
                    .then(response => response.json())
                    .then(data => {
                        // 1. UPDATE STATISTIK (CARD)
                        const mappings = {
                            'total-antrian-count': data.totalAntrian,
                            'menunggu-count': data.menunggu,
                            'dipanggil-count': data.dipanggil,
                            'diproses-count': data.selesaidiproses,
                            'pengambilan-count': data.pengambilanDokumen,
                            'selesai-count': data.selesai,
                            'dilewati-count': data.dilewati 
                        };

                        for (const [id, value] of Object.entries(mappings)) {
                            const el = document.getElementById(id);
                            if (el) el.innerText = value ?? 0;
                        }

                        // 2. UPDATE UI STATUS SISTEM SECARA LIVE
                        const statusContainer = document.getElementById('status-system-container');
                        const btnToggle = document.getElementById('btn-toggle');
                        const statusInput = document.getElementById('status-input');

                        if (data.systemStatus === 'off') {
                            statusContainer.innerHTML = `<div class="alert alert-danger d-inline-block px-5 rounded-pill animate-pulse">
                                <i class="fas fa-exclamation-triangle me-2"></i> <strong>SISTEM SEDANG DIHENTIKAN</strong>
                            </div>`;
                            btnToggle.className = "btn btn-success px-4 py-2 fw-bold rounded-pill shadow-sm";
                            btnToggle.innerHTML = `<i class="fas fa-play me-2"></i> Aktifkan Antrian`;
                            statusInput.value = "on";
                        } else {
                            statusContainer.innerHTML = `<div class="alert alert-success d-inline-block px-5 rounded-pill">
                                <i class="fas fa-check-circle me-2"></i> <strong>SISTEM BERJALAN NORMAL</strong>
                            </div>`;
                            btnToggle.className = "btn btn-danger px-4 py-2 fw-bold rounded-pill shadow-sm";
                            btnToggle.innerHTML = `<i class="fas fa-stop-circle me-2"></i> Hentikan Antrian`;
                            statusInput.value = "off";
                        }

                        // 3. UPDATE TABEL MONITORING
                        updateTable(data.dataAntrian);
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }

            function updateTable(antrians) {
                const tbody = document.getElementById('antrian-table-body');
                if (!tbody || !antrians) return;

                if (antrians.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada antrian hari ini.</td></tr>';
                    return;
                }

                let html = '';
                antrians.forEach(item => {
                    let badgeClass = 'bg-secondary';
                    let displayStatus = item.status.toLowerCase();
                    let displayLabel = item.status.replace(/_/g, ' ').toUpperCase();
                    let icon = '';

                    if (displayStatus === 'menunggu') {
                        badgeClass = 'bg-secondary';
                    } else if (displayStatus === 'dipanggil') {
                        badgeClass = 'bg-info text-white animate-pulse';
                        icon = '<i class="fas fa-volume-up me-1"></i>';
                    } else if (displayStatus === 'selesai diproses') {
                        badgeClass = 'bg-primary';
                    } else if (displayStatus === 'pengambilan_dokumen') {
                        badgeClass = 'bg-warning text-dark border border-warning';
                        displayLabel = 'PENGAMBILAN DOKUMEN';
                        icon = '<i class="fas fa-file-signature me-1"></i>';
                    } else if (displayStatus === 'selesai') {
                        badgeClass = 'bg-success';
                    } else if (displayStatus === 'dilewati' || displayStatus === 'lewat') {
                        badgeClass = 'bg-danger';
                        displayLabel = 'DILEWATI';
                    }

                    const loketInfo = item.user_id 
                        ? `<div class="small fw-bold text-dark"><i class="fas fa-desktop me-1"></i> ${item.loket ? item.loket.nama_loket : 'Loket'}</div>
                           <div class="small text-muted"><i class="fas fa-user-tie me-1"></i> ${item.petugas ? item.petugas.name : '-'}</div>`
                        : `<span class="text-muted small italic">Belum dipanggil</span>`;

                    // Format jam
                    const dateObj = new Date(item.created_at);
                    const waktu = dateObj.getHours().toString().padStart(2, '0') + ':' + dateObj.getMinutes().toString().padStart(2, '0');

                    html += `
                        <tr>
                            <td class="ps-4 fw-bold text-primary">${item.nomor_antrian}</td>
                            <td>${item.nama_pendaftar}</td>
                            <td><span class="badge bg-light text-dark border">${item.layanan ? item.layanan.nama_layanan : '-'}</span></td>
                            <td><span class="badge ${badgeClass}">${icon}${displayLabel}</span></td>
                            <td>${loketInfo}</td>
                            <td class="text-end pe-4 small text-muted">${waktu}</td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            }

            // Jalankan update setiap 3 detik
            setInterval(fetchRealtimeData, 3000);
        });
    </script>

    <style>
        .fa-spin-slow { animation: fa-spin 3s infinite linear; }
        .badge { font-weight: 600; padding: 0.5em 0.8em; border-radius: 6px; }
        .card { transition: all 0.3s ease; }
        .card:hover { transform: translateY(-4px); }
        @keyframes pulse-opacity { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }
        .animate-pulse { animation: pulse-opacity 1.5s infinite ease-in-out; }
    </style>
@endsection