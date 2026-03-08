@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <h2 class="fw-bold text-dark"><i class="fas fa-gauge-high me-2"></i> Dashboard Admin</h2>
        <p class="text-muted">Pantau seluruh aktivitas antrian Dukcapil di sini secara realtime.</p>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white border-0 shadow">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Total Antrian</h6>
                            <h1 class="display-4 fw-bold" id="total-antrian-count">{{ $totalAntrian ?? 0 }}</h1>
                        </div>
                        <i class="fas fa-users fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white border-0 shadow">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Selesai Dilayani</h6>
                            <h1 class="display-4 fw-bold" id="selesai-count">{{ $selesai ?? 0 }}</h1>
                        </div>
                        <i class="fas fa-check-double fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white border-0 shadow">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Dilewati</h6>
                            <h1 class="display-4 fw-bold" id="lewat-count">{{ $lewat ?? 0 }}</h1>
                        </div>
                        <i class="fas fa-times-circle fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pusat Manajemen --}}
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body text-center py-5">
            <h5 class="fw-bold mb-4 text-dark">Pusat Manajemen</h5>
            <div class="d-flex justify-content-center gap-3 flex-wrap">

                <a href="{{ route('admin.layanan') }}"
                    class="btn btn-outline-success px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-concierge-bell me-2"></i> Kelola Layanan
                </a>
                
                {{-- Kelola Petugas --}}
                <a href="{{ route('admin.petugas') }}"
                    class="btn btn-outline-primary px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-user-cog me-2"></i> Kelola Petugas
                </a>

                {{-- Kelola Antrian --}}
                <a href="{{ route('admin.antrian') }}"
                    class="btn btn-outline-info px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-users-viewfinder me-2"></i> Kelola Antrian
                </a>

                {{-- Cek Display --}}
                <a href="{{ route('display') }}" target="_blank"
                    class="btn btn-outline-dark px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-external-link-alt me-2"></i> Cek Display
                </a>

                {{-- Reset Display & Statistik (Reset ke 0) --}}
                <a href="{{ route('admin.reset-display') }}"
                    onclick="return confirm('PERHATIAN: Ini akan menghapus seluruh data antrian hari ini dan mereset nomor mulai dari 1. Lanjutkan?')"
                    class="btn btn-outline-danger px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-rotate me-2"></i> Reset Sistem (Mulai 0)
                </a>
            </div>
        </div>
    </div>

    {{-- Alert Sukses Jika Ada --}}
    @if(session('success'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        </script>
    @endif

    {{-- Script Realtime Update --}}
    <script>
        /**
         * Fungsi untuk mengambil data terbaru dari server tanpa refresh
         */
        function fetchRealtimeStats() {
            fetch("{{ route('admin.realtime-stats') }}")
                .then(response => response.json())
                .then(data => {
                    // Update elemen angka dengan ID masing-masing
                    document.getElementById('total-antrian-count').innerText = data.totalAntrian;
                    document.getElementById('selesai-count').innerText = data.selesai;
                    document.getElementById('lewat-count').innerText = data.lewat;
                })
                .catch(error => console.error('Error fetching stats:', error));
        }

        // Jalankan pengecekan setiap 3 detik (3000ms)
        setInterval(fetchRealtimeStats, 3000);
    </script>

@endsection